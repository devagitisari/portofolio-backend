<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GitHubActivityController extends Controller
{
    public function index()
    {
        $settings = Setting::first();

        if (!$settings?->show_github_activity || !$settings?->github) {
            return response()->json([
                'data' => [
                    'enabled' => false,
                    'username' => null,
                    'events' => [],
                ],
            ]);
        }

        $username = $this->extractUsername($settings->github);

        if (!$username) {
            return response()->json([
                'data' => [
                    'enabled' => true,
                    'username' => null,
                    'events' => [],
                ],
            ]);
        }

        $events = Cache::store('file')->remember("github_activity_{$username}", now()->addMinutes(15), function () use ($username) {
            try {
                $response = Http::acceptJson()
                    ->withUserAgent('DevaPortfolio/1.0')
                    ->timeout(8)
                    ->get("https://api.github.com/users/{$username}/events/public");

                if (!$response->successful()) {
                    return [];
                }
            } catch (Throwable) {
                return [];
            }

            return collect($response->json())
                ->take(8)
                ->map(fn (array $event) => [
                    'id' => $event['id'] ?? null,
                    'type' => $event['type'] ?? 'GitHubEvent',
                    'repo' => $event['repo']['name'] ?? null,
                    'createdAt' => $event['created_at'] ?? null,
                    'summary' => $this->summarize($event),
                    'url' => isset($event['repo']['name']) ? "https://github.com/{$event['repo']['name']}" : "https://github.com/{$username}",
                ])
                ->values()
                ->all();
        });

        return response()->json([
            'data' => [
                'enabled' => true,
                'username' => $username,
                'events' => $events,
            ],
        ]);
    }

    public function contributions()
    {
        $settings = Setting::first();

        if (!$settings?->show_github_activity || !$settings?->github) {
            return response('', 204);
        }

        $username = $this->extractUsername($settings->github);
        $theme = request('theme', 'dark'); // Get theme parameter

        if (!$username) {
            return response('', 204);
        }

        $html = Cache::store('file')->remember("github_contributions_{$username}_{$theme}", now()->addMinutes(30), function () use ($username, $theme) {
            try {
                $response = Http::accept('text/html')
                    ->withUserAgent('DevaPortfolio/1.0')
                    ->timeout(10)
                    ->get("https://github.com/users/{$username}/contributions");

                if (!$response->successful()) {
                    return '';
                }

                return $response->body();
            } catch (Throwable) {
                return '';
            }
        });

        if ($html === '') {
            return response('', 204);
        }

        return response($this->wrapContributionsHtml($html, $username, $theme), 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function extractUsername(string $github): ?string
    {
        $github = trim($github);

        if (Str::startsWith($github, '@')) {
            return ltrim($github, '@');
        }

        $path = parse_url($github, PHP_URL_PATH);
        $candidate = $path ? trim($path, '/') : $github;
        $segments = explode('/', $candidate);

        return $segments[0] !== '' ? $segments[0] : null;
    }

    private function summarize(array $event): string
    {
        $repo = $event['repo']['name'] ?? 'a repository';
        $type = $event['type'] ?? '';

        return match ($type) {
            'PushEvent' => 'Pushed commits to ' . $repo,
            'PullRequestEvent' => ucfirst($event['payload']['action'] ?? 'updated') . ' a pull request in ' . $repo,
            'IssuesEvent' => ucfirst($event['payload']['action'] ?? 'updated') . ' an issue in ' . $repo,
            'CreateEvent' => 'Created ' . ($event['payload']['ref_type'] ?? 'something') . ' in ' . $repo,
            'ForkEvent' => 'Forked ' . $repo,
            'WatchEvent' => 'Starred ' . $repo,
            'IssueCommentEvent' => 'Commented on an issue in ' . $repo,
            default => Str::headline(Str::replaceEnd('Event', '', $type)) . ' in ' . $repo,
        };
    }

    private function wrapContributionsHtml(string $html, string $username, string $theme = 'dark'): string
    {
        $isLightTheme = $theme === 'light';
        $colorScheme = $isLightTheme ? 'light' : 'dark';
        
        return <<<HTML
<!doctype html>
<html lang="en" data-theme="{$theme}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base target="_blank">
  <style>
    :root { color-scheme: {$colorScheme}; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      background: transparent;
      color: {$this->getBodyColor($isLightTheme)};
      font: 12px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      overflow-x: auto;
      overflow-y: hidden;
    }
    
    a { color: {$this->getLinkColor($isLightTheme)}; text-decoration: none; }
    a[href^="#year-link"],
    a[href*="why-are-my-contributions-not-showing-up"] {
      display: none !important;
    }
    .contrib-column,
    .contribution-activity-listing,
    .js-yearly-contributions .contrib-footer,
    .js-yearly-contributions .sr-only,
    .js-calendar-graph > h2 {
      display: none !important;
    }
    .js-yearly-contributions h2 {
      margin: 0 0 14px;
      color: {$this->getHeadingColor($isLightTheme)};
      font-size: 18px;
      font-weight: 400;
      line-height: 1.5;
    }
    .border {
      border: 1px solid {$this->getBorderColor($isLightTheme)};
      border-radius: 6px;
    }
    .py-2 {
      padding-top: 14px;
      padding-bottom: 14px;
    }
    .js-calendar-graph,
    .calendar-graph {
      overflow: visible !important;
      padding: 0 !important;
    }
    .ContributionCalendar-grid {
      width: max-content;
      border-spacing: 5px !important;
    }
    .ContributionCalendar-label {
      color: {$this->getLabelColor($isLightTheme)};
      fill: {$this->getLabelColor($isLightTheme)};
      font-size: 12px;
    }
    .ContributionCalendar-grid tr {
      height: 16px !important;
    }
    .ContributionCalendar-day {
      shape-rendering: geometricPrecision;
      width: 15px !important;
      height: 15px !important;
      border-radius: 3px;
      background-color: {$this->getContribLevel($isLightTheme, 0)};
      outline: 1px solid {$this->getOutlineColor($isLightTheme)};
      outline-offset: -1px;
    }
    .ContributionCalendar-day[data-level="0"] { background-color: {$this->getContribLevel($isLightTheme, 0)}; }
    .ContributionCalendar-day[data-level="1"] { background-color: {$this->getContribLevel($isLightTheme, 1)}; }
    .ContributionCalendar-day[data-level="2"] { background-color: {$this->getContribLevel($isLightTheme, 2)}; }
    .ContributionCalendar-day[data-level="3"] { background-color: {$this->getContribLevel($isLightTheme, 3)}; }
    .ContributionCalendar-day[data-level="4"] { background-color: {$this->getContribLevel($isLightTheme, 4)}; }
    .width-full {
      width: 100%;
    }
    .f6 {
      color: {$this->getLabelColor($isLightTheme)};
      font-size: 13px;
    }
    .float-left {
      float: left;
      margin-left: 40px;
      margin-top: 12px;
    }
    .float-right {
      float: right;
      display: flex;
      align-items: center;
      gap: 4px;
      margin-right: 10px;
      margin-top: 12px;
    }
    .float-right .ContributionCalendar-day {
      display: inline-block;
      width: 10px !important;
      height: 10px !important;
      margin: 0 1px;
    }
    .js-yearly-contributions {
      min-width: 1010px;
      width: max-content;
      margin: 0 auto;
      padding-bottom: 28px;
    }
  </style>
</head>
<body>
  {$html}
</body>
</html>
HTML;
    }

    private function getBodyColor(bool $isLight): string
    {
        return $isLight ? '#24292f' : '#c9d1d9';
    }

    private function getLinkColor(bool $isLight): string
    {
        return $isLight ? '#0969da' : '#58a6ff';
    }

    private function getHeadingColor(bool $isLight): string
    {
        return $isLight ? '#24292f' : '#c9d1d9';
    }

    private function getBorderColor(bool $isLight): string
    {
        return $isLight ? '#d0d7de' : '#30363d';
    }

    private function getLabelColor(bool $isLight): string
    {
        return $isLight ? '#656d76' : '#8b949e';
    }

    private function getOutlineColor(bool $isLight): string
    {
        return $isLight ? 'rgba(27, 31, 36, 0.06)' : 'rgba(240, 246, 252, 0.06)';
    }

    private function getContribLevel(bool $isLight, int $level): string
    {
        if ($isLight) {
            return match ($level) {
                0 => '#ebedf0',
                1 => '#9be9a8',
                2 => '#40c463',
                3 => '#30a14e',
                4 => '#216e39',
                default => '#ebedf0'
            };
        } else {
            return match ($level) {
                0 => '#161b22',
                1 => '#0e4429',
                2 => '#006d32',
                3 => '#26a641',
                4 => '#39d353',
                default => '#161b22'
            };
        }
    }
}
