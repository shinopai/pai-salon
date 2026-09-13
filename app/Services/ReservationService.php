<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\BusinessHour;
use App\Models\Customer;
use App\Models\Holiday;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Mail\ReservationConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReservationService
{
    /**
     * 予約を登録する。
     *
     * @param array{
     *     staff_id: int,
     *     menu_id: int,
     *     start_at: CarbonInterface|string,
     *     customer_name: string,
     *     customer_email: string
     * } $data
     */
    public function reserve(array $data): Reservation
    {
        $menu = Menu::query()->findOrFail($data['menu_id']);
        $staff = Staff::query()->findOrFail($data['staff_id']);

        $startAt = $data['start_at'] instanceof CarbonInterface
            ? $data['start_at']->copy()
            : Carbon::parse($data['start_at']);

        $this->validateReservationAvailability(
            $staff,
            $menu,
            $startAt,
        );

        $endAt = $startAt->copy()->addMinutes($menu->duration);

        $cancellationToken = $this->generateCancellationToken();

        $reservation = DB::transaction(function () use (
            $data,
            $staff,
            $menu,
            $startAt,
            $endAt,
            $cancellationToken
        ) {
            $lockedStaff = Staff::query()
                ->whereKey($staff->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingReservations = Reservation::query()
                ->where('staff_id', $lockedStaff->id)
                ->where('status', '!=', ReservationStatus::CANCELLED)
                ->where('start_at', '<', $endAt)
                ->where('end_at', '>', $startAt)
                ->get();

            if ($existingReservations->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'start_at' => '選択した時間帯はすでに予約されています。',
                ]);
            }

            $customer = Customer::query()->firstOrCreate(
                ['email' => $data['customer_email']],
                ['name' => $data['customer_name']]
            );

            $reservation = new Reservation();
            $reservation->reservation_number = $this->generateReservationNumber($startAt);
            $reservation->cancellation_token = Hash::make($cancellationToken);
            $reservation->customer_id = $customer->id;
            $reservation->customer_name = $data['customer_name'];
            $reservation->customer_email = $data['customer_email'];
            $reservation->staff_id = $lockedStaff->id;
            $reservation->menu_id = $menu->id;
            $reservation->start_at = $startAt;
            $reservation->end_at = $endAt;
            $reservation->status = ReservationStatus::RESERVED;

            $reservation->save();

            return $reservation;
        });

        try {
            Mail::to($reservation->customer_email)
                ->send(new ReservationConfirmationMail(
                    $reservation,
                    $cancellationToken,
                ));
        } catch (\Throwable $e) {
            Log::error('予約確認メールの送信に失敗しました。', [
                'reservation_number' => $reservation->reservation_number,
                'customer_email' => $reservation->customer_email,
                'error' => $e->getMessage(),
            ]);
        }

        return $reservation;
    }

    /**
     * 予約可能条件を確定時点で再判定する。
     */
    private function validateReservationAvailability(
        Staff $staff,
        Menu $menu,
        CarbonInterface $startAt,
    ): void {
        if (! $this->isStaffAvailableForMenu($staff, $menu->id)) {
            throw ValidationException::withMessages([
                'staff_id' => '選択したスタッフはこのメニューに対応していません。',
            ]);
        }

        if ($this->isHoliday($startAt)) {
            throw ValidationException::withMessages([
                'start_at' => '選択した日は休業日です。',
            ]);
        }

        $businessHours = $this->getBusinessHours($startAt);

        if (
            $businessHours === null
            || $businessHours['open_time'] === null
            || $businessHours['close_time'] === null
        ) {
            throw ValidationException::withMessages([
                'start_at' => '選択した日時は営業日ではありません。',
            ]);
        }

        $endAt = $startAt->copy()->addMinutes($menu->duration);

        $openAt = $startAt->copy()
            ->setTimeFromTimeString($businessHours['open_time']);

        $closeAt = $startAt->copy()
            ->setTimeFromTimeString($businessHours['close_time']);

        if ($startAt < $openAt || $endAt > $closeAt) {
            throw ValidationException::withMessages([
                'start_at' => '選択した日時は営業時間外です。',
            ]);
        }

        if (! $this->isWithinBookingPeriod($startAt)) {
            throw ValidationException::withMessages([
                'start_at' => '選択した日時は予約可能期間外です。',
            ]);
        }

        if (! $this->isWithinSameDayBookingLimit($startAt)) {
            throw ValidationException::withMessages([
                'start_at' => '当日の予約は開始時刻の3時間前まで受け付けています。',
            ]);
        }
    }

    /**
     * スタッフが指定されたメニューに対応可能か判定する。
     */
    private function isStaffAvailableForMenu(
        Staff $staff,
        int $menuId,
    ): bool {
        return $staff->menus()
            ->whereKey($menuId)
            ->exists();
    }

    /**
     * 指定日の営業時間を取得する。
     *
     * @return array{open_time: ?string, close_time: ?string}|null
     */
    private function getBusinessHours(
        CarbonInterface $date,
    ): ?array {
        $businessHour = BusinessHour::query()
            ->where('day_of_week', $date->dayOfWeek)
            ->first();

        if ($businessHour === null) {
            return null;
        }

        return [
            'open_time' => $businessHour->open_time,
            'close_time' => $businessHour->close_time,
        ];
    }

    /**
     * 指定日が臨時休業日か判定する。
     */
    private function isHoliday(CarbonInterface $date): bool
    {
        return Holiday::query()
            ->whereDate('date', $date)
            ->exists();
    }

    /**
     * 指定日が予約可能期間内か判定する。
     */
    private function isWithinBookingPeriod(CarbonInterface $date): bool
    {
        $today = now()->startOfDay();
        $maxDate = $today->copy()->addMonthsNoOverflow(2);

        return $date->copy()
            ->startOfDay()
            ->betweenIncluded($today, $maxDate);
    }

    /**
     * 予約番号を生成する。
     */
    private function generateReservationNumber(
        CarbonInterface $startAt,
    ): string {
        do {
            $reservationNumber = 'RSV-' . $startAt->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (
            Reservation::query()
            ->where('reservation_number', $reservationNumber)
            ->exists()
        );

        return $reservationNumber;
    }

    /**
     * キャンセルトークンを生成する。
     */
    private function generateCancellationToken(): string
    {
        return Str::random(64);
    }

    /**
     * 当日の予約開始時刻が現在時刻から3時間以上先か判定する。
     */
    private function isWithinSameDayBookingLimit(
        CarbonInterface $startAt,
    ): bool {
        if (! $startAt->isToday()) {
            return true;
        }

        return $startAt->greaterThanOrEqualTo(
            now()->addHours(3),
        );
    }
}
