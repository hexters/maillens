<?php

namespace Hexters\MailLens\Http\Controllers;

use Hexters\MailLens\Models\MailLensMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MailLensController
{
    public function index(Request $request)
    {
        $messages = MailLensMessage::query()
            ->orderByDesc('id')
            ->get();

        $selected = null;

        if ($request->filled('m')) {
            $selected = $messages->firstWhere('id', (int) $request->query('m'))
                ?? MailLensMessage::find($request->query('m'));
        }

        $selected ??= $messages->first();

        if ($selected && ! $selected->read) {
            $selected->forceFill(['read' => true])->save();
        }

        return view('maillens::index', [
            'messages' => $messages,
            'selected' => $selected,
        ]);
    }

    public function logo(): Response
    {
        return response(
            file_get_contents(__DIR__ . '/../../Resources/logo.png'),
            200,
            ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=604800'],
        );
    }

    public function html(MailLensMessage $message): Response
    {
        $html = $message->html ?: nl2br(e($message->text ?? '(no content)'));

        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function source(MailLensMessage $message): Response
    {
        return response($message->raw ?? '', 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function attachment(MailLensMessage $message, int $index): Response
    {
        $attachment = $message->attachments[$index] ?? abort(404);

        return response(base64_decode($attachment['content']), 200, [
            'Content-Type' => $attachment['content_type'] ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . ($attachment['filename'] ?? 'attachment') . '"',
        ]);
    }

    public function destroy(MailLensMessage $message)
    {
        $message->delete();

        return redirect()->route('maillens.index');
    }

    public function clear()
    {
        MailLensMessage::query()->delete();

        return redirect()->route('maillens.index');
    }
}
