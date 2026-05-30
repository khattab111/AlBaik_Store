<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocumentationController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(auth()->user()?->canAccessPanel(filament()->getPanel('admin')), 403);

        $path = base_path('docs/ADMIN_STORE_DOCUMENTATION.md');
        abort_unless(File::exists($path), 404);

        $markdown = File::get($path);
        $headings = $this->extractHeadings($markdown);

        return view('admin.documentation', [
            'html' => $this->addHeadingIds((string) Str::markdown($markdown, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]), $headings),
            'headings' => $headings,
        ]);
    }

    /**
     * @return array<int, array{level: int, title: string, id: string}>
     */
    private function extractHeadings(string $markdown): array
    {
        preg_match_all('/^(#{1,3})\s+(.+)$/m', $markdown, $matches, PREG_SET_ORDER);

        $headings = [];
        $usedIds = [];

        foreach ($matches as $match) {
            $level = strlen($match[1]);
            $title = trim(preg_replace('/[`*_#\[\]]/', '', $match[2]));

            if ($title === '') {
                continue;
            }

            $baseId = Str::slug($title);

            if ($baseId === '') {
                $baseId = 'section-'.(count($headings) + 1);
            }

            $id = $baseId;
            $counter = 2;

            while (isset($usedIds[$id])) {
                $id = $baseId.'-'.$counter;
                $counter++;
            }

            $usedIds[$id] = true;

            $headings[] = [
                'level' => $level,
                'title' => $title,
                'id' => $id,
            ];
        }

        return $headings;
    }

    /**
     * @param  array<int, array{level: int, title: string, id: string}>  $headings
     */
    private function addHeadingIds(string $html, array $headings): string
    {
        $index = 0;

        return preg_replace_callback('/<h([1-3])>(.*?)<\/h\1>/s', function (array $matches) use ($headings, &$index): string {
            $heading = $headings[$index] ?? null;
            $index++;

            if (! $heading) {
                return $matches[0];
            }

            return sprintf(
                '<h%s id="%s">%s</h%s>',
                $matches[1],
                e($heading['id']),
                $matches[2],
                $matches[1],
            );
        }, $html) ?? $html;
    }
}
