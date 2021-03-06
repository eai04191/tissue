<?php

namespace App\Http\Controllers;

use App\Ejaculation;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class UserController extends Controller
{
    public function redirectMypage()
    {
        return redirect()->route('user.profile', ['name' => auth()->user()->name]);
    }

    public function profile($name)
    {
        $user = User::where('name', $name)->first();
        if (empty($user)) {
            abort(404);
        }

        // チェックインの取得
        $query = Ejaculation::select(DB::raw(
            <<<'SQL'
id,
ejaculated_date,
note,
is_private,
is_too_sensitive,
link,
source,
to_char(lead(ejaculated_date, 1, NULL) OVER (ORDER BY ejaculated_date DESC), 'YYYY/MM/DD HH24:MI') AS before_date,
to_char(ejaculated_date - (lead(ejaculated_date, 1, NULL) OVER (ORDER BY ejaculated_date DESC)), 'FMDDD日 FMHH24時間 FMMI分') AS ejaculated_span
SQL
        ))
            ->where('user_id', $user->id);
        if (!Auth::check() || $user->id !== Auth::id()) {
            $query = $query->where('is_private', false);
        }
        $ejaculations = $query->orderBy('ejaculated_date', 'desc')
            ->with('tags')
            ->withLikes()
            ->paginate(20);

        // よく使っているタグ
        $tagsQuery = DB::table('ejaculations')
            ->join('ejaculation_tag', 'ejaculations.id', '=', 'ejaculation_tag.ejaculation_id')
            ->join('tags', 'ejaculation_tag.tag_id', '=', 'tags.id')
            ->selectRaw('tags.name, count(*) as count')
            ->where('ejaculations.user_id', $user->id);
        if (!Auth::check() || $user->id !== Auth::id()) {
            $tagsQuery = $tagsQuery->where('ejaculations.is_private', false);
        }
        $tags = $tagsQuery->groupBy('tags.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // シコ草
        $countByDayQuery = $this->countEjaculationByDay($user)
            ->where('ejaculated_date', '>=', now()->startOfMonth()->subMonths(9))
            ->where('ejaculated_date', '<', now()->addMonth()->startOfMonth())
            ->get();
        $countByDay = [];
        foreach ($countByDayQuery as $data) {
            $date = Carbon::createFromFormat('Y/m/d', $data->date);
            $countByDay[$date->timestamp] = $data->count;
        }

        return view('user.profile')->with(compact('user', 'ejaculations', 'tags', 'countByDay'));
    }

    public function stats($name)
    {
        $user = User::where('name', $name)->first();
        if (empty($user)) {
            abort(404);
        }

        $availableMonths = $this->makeStatsAvailableMonths($user);
        $graphData = $this->makeGraphData($user);

        return view('user.stats.index')->with(compact('user', 'graphData', 'availableMonths'));
    }

    public function statsYearly($name, $year)
    {
        $user = User::where('name', $name)->first();
        if (empty($user)) {
            abort(404);
        }

        $validator = Validator::make(compact('year'), [
            'year' => 'required|date_format:Y'
        ]);
        if ($validator->fails()) {
            return redirect()->route('user.stats', compact('name'));
        }

        $availableMonths = $this->makeStatsAvailableMonths($user);
        if (!isset($availableMonths[$year])) {
            return redirect()->route('user.stats', compact('name'));
        }

        $graphData = $this->makeGraphData(
            $user,
            Carbon::createFromDate($year, 1, 1, config('app.timezone'))->startOfDay(),
            Carbon::createFromDate($year, 1, 1, config('app.timezone'))->addYear()->startOfDay()
        );

        return view('user.stats.yearly')
            ->with(compact('user', 'graphData', 'availableMonths'))
            ->with('currentYear', (int) $year);
    }

    public function statsMonthly($name, $year, $month)
    {
        $user = User::where('name', $name)->first();
        if (empty($user)) {
            abort(404);
        }

        $validator = Validator::make(compact('year', 'month'), [
            'year' => 'required|date_format:Y',
            'month' => 'required|date_format:m'
        ]);
        if ($validator->fails()) {
            return redirect()->route('user.stats.yearly', compact('name', 'year'));
        }

        $availableMonths = $this->makeStatsAvailableMonths($user);
        if (!isset($availableMonths[$year]) || !in_array($month, $availableMonths[$year], false)) {
            return redirect()->route('user.stats.yearly', compact('name', 'year'));
        }

        $graphData = $this->makeGraphData(
            $user,
            Carbon::createFromDate($year, $month, 1, config('app.timezone'))->startOfDay(),
            Carbon::createFromDate($year, $month, 1, config('app.timezone'))->addMonth()->startOfDay()
        );

        return view('user.stats.monthly')
            ->with(compact('user', 'graphData', 'availableMonths'))
            ->with('currentYear', (int) $year)
            ->with('currentMonth', (int) $month);
    }

    public function okazu($name)
    {
        $user = User::where('name', $name)->first();
        if (empty($user)) {
            abort(404);
        }

        // チェックインの取得
        $query = Ejaculation::select(DB::raw(
            <<<'SQL'
id,
ejaculated_date,
note,
is_private,
is_too_sensitive,
link,
source,
to_char(lead(ejaculated_date, 1, NULL) OVER (ORDER BY ejaculated_date DESC), 'YYYY/MM/DD HH24:MI') AS before_date,
to_char(ejaculated_date - (lead(ejaculated_date, 1, NULL) OVER (ORDER BY ejaculated_date DESC)), 'FMDDD日 FMHH24時間 FMMI分') AS ejaculated_span
SQL
        ))
            ->where('user_id', $user->id)
            ->where('link', '<>', '');
        if (!Auth::check() || $user->id !== Auth::id()) {
            $query = $query->where('is_private', false);
        }
        $ejaculations = $query->orderBy('ejaculated_date', 'desc')
            ->with('tags')
            ->withLikes()
            ->paginate(20);

        return view('user.profile')->with(compact('user', 'ejaculations'));
    }

    public function likes($name)
    {
        $user = User::where('name', $name)->first();
        if (empty($user)) {
            abort(404);
        }

        $likes = $user->likes()
            ->orderBy('created_at', 'desc')
            ->with('ejaculation.user', 'ejaculation.tags')
            ->whereHas('ejaculation', function ($query) {
                $query->where('user_id', Auth::id())
                    ->orWhere('is_private', false);
            })
            ->paginate(20);

        return view('user.likes')->with(compact('user', 'likes'));
    }

    private function makeStatsAvailableMonths(User $user): array
    {
        $availableMonths = [];
        $oldest = $user->ejaculations()->orderBy('ejaculated_date')->first();
        if (isset($oldest)) {
            $oldestMonth = $oldest->ejaculated_date->startOfMonth();
            $currentMonth = now()->startOfMonth();
            for ($month = $currentMonth; $oldestMonth <= $currentMonth; $month = $month->subMonth()) {
                if (!isset($availableMonths[$month->year])) {
                    $availableMonths[$month->year] = [];
                }
                $availableMonths[$month->year][] = $month->month;
            }
        }

        return $availableMonths;
    }

    private function makeGraphData(User $user, CarbonInterface $dateSince = null, CarbonInterface $dateUntil = null): array
    {
        if ($dateUntil === null) {
            $dateUntil = now()->addMonth()->startOfMonth();
        }
        $dateCondition = [
            ['ejaculated_date', '<', $dateUntil],
        ];
        if ($dateSince !== null) {
            $dateCondition[] = ['ejaculated_date', '>=', $dateSince];
        }

        $groupByDay = $this->countEjaculationByDay($user)
            ->where($dateCondition)
            ->get();

        $groupByHour = Ejaculation::select(DB::raw(
            <<<'SQL'
to_char(ejaculated_date, 'HH24') AS "hour",
count(*) AS "count"
SQL
        ))
            ->where('user_id', $user->id)
            ->where($dateCondition)
            ->groupBy(DB::raw("to_char(ejaculated_date, 'HH24')"))
            ->orderBy(DB::raw('1'))
            ->get();

        $dailySum = [];
        $monthlySum = [];
        $yearlySum = [];
        $dowSum = array_fill(0, 7, 0);
        $hourlySum = array_fill(0, 24, 0);

        // 年間グラフ用の配列初期化
        if ($groupByDay->first() !== null) {
            $year = Carbon::createFromFormat('Y/m/d', $groupByDay->first()->date)->year;
            $currentYear = date('Y');
            for (; $year <= $currentYear; $year++) {
                $yearlySum[$year] = 0;
            }
        }

        foreach ($groupByDay as $data) {
            $date = Carbon::createFromFormat('Y/m/d', $data->date);
            $yearAndMonth = $date->format('Y/m');

            $dailySum[$date->timestamp] = $data->count;
            $yearlySum[$date->year] += $data->count;
            $dowSum[$date->dayOfWeek] += $data->count;
            $monthlySum[$yearAndMonth] = ($monthlySum[$yearAndMonth] ?? 0) + $data->count;
        }

        foreach ($groupByHour as $data) {
            $hour = (int)$data->hour;
            $hourlySum[$hour] += $data->count;
        }

        return [
            'dailySum' => $dailySum,
            'dowSum' => $dowSum,
            'monthlySum' => $monthlySum,
            'yearlyKey' => array_keys($yearlySum),
            'yearlySum' => array_values($yearlySum),
            'hourlyKey' => array_keys($hourlySum),
            'hourlySum' => array_values($hourlySum),
        ];
    }

    private function countEjaculationByDay(User $user)
    {
        return Ejaculation::select(DB::raw(
            <<<'SQL'
to_char(ejaculated_date, 'YYYY/MM/DD') AS "date",
count(*) AS "count"
SQL
        ))
            ->where('user_id', $user->id)
            ->groupBy(DB::raw("to_char(ejaculated_date, 'YYYY/MM/DD')"))
            ->orderBy(DB::raw("to_char(ejaculated_date, 'YYYY/MM/DD')"));
    }
}
