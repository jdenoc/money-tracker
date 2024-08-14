<?php

namespace App\Jobs;

use App\Models\Attachment;
use App\Models\Entry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class AttachAttachmentToEntry implements ShouldQueue {
    use Dispatchable;

    private Entry $entry;
    private array $entry_attachments;

    /**
     * Create a new job instance.
     */
    public function __construct($entryId, $entryAttachments) {
        $this->entry = Entry::find($entryId);
        $this->entry_attachments = $entryAttachments;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        Log::debug(class_basename(__CLASS__)." job running [entryId:{$this->entry->id}]");
        foreach ($this->entry_attachments as $attachment_data) {
            if (!is_array($attachment_data)) {
                continue;
            }

            $existing_attachment = Attachment::find($attachment_data['uuid']);
            if (is_null($existing_attachment)) {
                $new_attachment = new Attachment();
                $new_attachment->uuid = $attachment_data['uuid'];
                $new_attachment->name = $attachment_data['name'];
                $new_attachment->entry_id = $this->entry->id;
                $new_attachment->storage_move_from_tmp_to_main();
                $new_attachment->save();
            }
        }
    }

}
