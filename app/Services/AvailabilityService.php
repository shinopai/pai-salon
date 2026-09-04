<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\BusinessHour;
use App\Models\Holiday;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
use Carbon\CarbonInterface;

class AvailabilityService
{
    /**
     * 指定されたメニュー・スタッフ・日付の空き枠を取得する。
     *
     * @return array<int, array{start_at: CarbonInterface, end_at: CarbonInterface}>
     */
    public function getAvailableSlots(
        int $menuId,
        int $staffId,
        CarbonInterface $date,
    ): array {
        $menu = Menu::query()->findOrFail($menuId);
        $staff = Staff::query()->findOrFail($staffId);

        if (! $this->isWithinBookingPeriod($date)) {
            return [];
        }

        if (! $this->isStaffAvailableForMenu($staff, $menuId)) {
            return [];
        }

        $businessHours = $this->getBusinessHours($date);

        if (
            $businessHours === null
            || $businessHours['open_time'] === null
            || $businessHours['close_time'] === null
        ) {
            return [];
        }

        if ($this->isHoliday($date)) {
            return [];
        }

        $openAt = $date->copy()->setTimeFromTimeString($businessHours['open_time']);
        $closeAt = $date->copy()->setTimeFromTimeString($businessHours['close_time']);

        $existingReservations = $this->getExistingReservations(
            $staffId,
            $date,
        );

        $slots = [];

        foreach ($this->generateCandidateSlots($openAt, $closeAt) as $startAt) {
            $endAt = $startAt->copy()->addMinutes($menu->duration);

            if ($endAt > $closeAt) {
                continue;
            }

            if (! $this->isWithinSameDayBookingLimit($startAt)) {
                continue;
            }

            if ($this->hasOverlap(
                $startAt,
                $endAt,
                $existingReservations,
            )) {
                continue;
            }

            $slots[] = [
                'start_at' => $startAt,
                'end_at' => $endAt,
            ];
        }

        return $slots;
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
    private function getBusinessHours(CarbonInterface $date): ?array
    {
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
            ->whereDate('date', $date->toDateString())
            ->exists();
    }

    /**
     * 指定スタッフの対象日の有効な予約を取得する。
     *
     * @return array<int, Reservation>
     */
    private function getExistingReservations(
        int $staffId,
        CarbonInterface $date,
    ): array {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        return Reservation::query()
            ->where('staff_id', $staffId)
            ->whereBetween('start_at', [$dayStart, $dayEnd])
            ->where('status', '!=', ReservationStatus::CANCELLED)
            ->get()
            ->all();
    }

    /**
     * 候補枠が既存予約と重複するか判定する。
     *
     * @param array<int, Reservation> $existingReservations
     */
    private function hasOverlap(
        CarbonInterface $startAt,
        CarbonInterface $endAt,
        array $existingReservations,
    ): bool {
        foreach ($existingReservations as $reservation) {
            if (
                $reservation->start_at < $endAt
                && $reservation->end_at > $startAt
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 指定された営業時間から30分刻みの開始時刻候補を生成する。
     *
     * @return array<int, CarbonInterface>
     */
    private function generateCandidateSlots(
        CarbonInterface $openAt,
        CarbonInterface $closeAt,
    ): array {
        $slots = [];

        $current = $openAt->copy();

        while ($current < $closeAt) {
            $slots[] = $current->copy();
            $current->addMinutes(30);
        }

        return $slots;
    }

    /**
     * 指定日が予約可能期間内か判定する。
     *
     * 当日から2か月先までを予約可能とする。
     */
    private function isWithinBookingPeriod(CarbonInterface $date): bool
    {
        $today = now()->startOfDay();
        $maxDate = $today->copy()->addMonthsNoOverflow(2);

        return $date->copy()->startOfDay()->betweenIncluded(
            $today,
            $maxDate,
        );
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
