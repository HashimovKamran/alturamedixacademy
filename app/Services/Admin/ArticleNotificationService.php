<?php

namespace App\Services\Admin;

use App\Models\Article;
use App\Models\ArticleNotification;
use App\Models\SiteUser;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ArticleNotificationService
{
    /**
     * @return array{sent:int,failed:int,skipped:int}
     */
    public function sendForArticle(Article $article): array
    {
        $result = ['sent' => 0, 'failed' => 0, 'skipped' => 0];

        $users = SiteUser::query()
            ->active()
            ->where('email_notify', true)
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->orderBy('id')
            ->get(['id', 'full_name', 'email']);

        foreach ($users as $user) {
            $email = mb_strtolower(trim((string) $user->email), 'UTF-8');

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result['skipped']++;
                continue;
            }

            $exists = ArticleNotification::query()
                ->where('article_id', $article->id)
                ->where('email', $email)
                ->exists();

            if ($exists) {
                $result['skipped']++;
                continue;
            }

            $status = 'sent';
            $error = null;

            try {
                $this->sendMail($article, (string) $user->full_name, $email);
                $result['sent']++;
            } catch (Throwable $exception) {
                $status = 'failed';
                $error = $exception->getMessage();
                $result['failed']++;
            }

            ArticleNotification::query()->create([
                'article_id' => $article->id,
                'user_id' => $user->id,
                'email' => $email,
                'status' => $status,
                'error_message' => $error,
                'sent_at' => $status === 'sent' ? now() : null,
            ]);
        }

        return $result;
    }

    private function sendMail(Article $article, string $fullName, string $email): void
    {
        $lang = (string) ($article->lang_code ?: 'az');
        $url = route('articles.show', ['slug' => (string) $article->slug, 'lang' => $lang]);
        $subject = 'Yeni məqalə: ' . (string) $article->title;
        $body = view('emails.article-notification', [
            'article' => $article,
            'fullName' => $fullName,
            'url' => $url,
        ])->render();

        Mail::html($body, function ($message) use ($email, $fullName, $subject): void {
            $message->to($email, $fullName ?: null)->subject($subject);
        });
    }
}
