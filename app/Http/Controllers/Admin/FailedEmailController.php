<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\Emails\EmailQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/** Admin > Failed emails: emails the worker couldn't send, with Retry and Delete. */
class FailedEmailController extends Controller
{
    public function index()
    {
        return view('admin.emails.failed', [
            'emails' => EmailQueue::failed(),
            'waiting' => EmailQueue::waiting(),
            'stuck' => EmailQueue::stuck(),
        ]);
    }

    public function retry(int $id)
    {
        $row = $this->find($id);
        $email = EmailQueue::describe($row);

        Artisan::call('queue:retry', ['id' => [$row->uuid]]);
        ActivityLog::record('email.retry', null, 'Retried failed email: ' . $email->type . ($email->to ? ' to ' . implode(', ', $email->to) : ''));

        return back()->with('success', 'Sending again: ' . $email->type . '.');
    }

    public function retryAll()
    {
        $uuids = DB::table($this->table())->where('queue', EmailQueue::QUEUE)->pluck('uuid')->filter()->values()->all();
        if ($uuids) {
            Artisan::call('queue:retry', ['id' => $uuids]);
            ActivityLog::record('email.retry_all', null, 'Retried ' . count($uuids) . ' failed ' . (count($uuids) === 1 ? 'email' : 'emails'));
        }

        return back()->with('success', count($uuids) . ' ' . (count($uuids) === 1 ? 'email is' : 'emails are') . ' being sent again.');
    }

    public function destroy(int $id)
    {
        $row = $this->find($id);
        $email = EmailQueue::describe($row);

        DB::table($this->table())->where('id', $row->id)->delete();
        ActivityLog::record('email.deleted', null, 'Deleted failed email: ' . $email->type . ($email->to ? ' to ' . implode(', ', $email->to) : ''));

        return back()->with('success', 'Deleted: ' . $email->type . '.');
    }

    private function table(): string
    {
        return config('queue.failed.table', 'failed_jobs');
    }

    private function find(int $id)
    {
        $row = DB::table($this->table())->where('id', $id)->where('queue', EmailQueue::QUEUE)->first();
        abort_unless($row, 404);

        return $row;
    }
}
