<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ManagesAttachments
{
    protected function setSingleAttachment(Model $model, string $relation, ?string $attachmentId, string $type): void
    {
        if (!$attachmentId) {
            $model->{$relation}()->delete();
            return;
        }

        $attachment = Attachment::find($attachmentId);
        if (!$attachment) {
            return;
        }

        $currentAttachment = $model->{$relation}()->first();
        if ($currentAttachment && $currentAttachment->id === $attachmentId) {
            return;
        }

        $model->{$relation}()->delete();

        $attachment->attachmentable()->associate($model);
        $attachment->type = $type;
        $attachment->save();
    }

    /**
     * @param array<int, string>|null $attachmentIds
     */
    protected function syncMultipleAttachments(Model $model, string $relation, ?array $attachmentIds, string $type): void
    {
        if (!is_array($attachmentIds)) {
            return;
        }

        $model->{$relation}()->whereNotIn('id', $attachmentIds)->delete();

        Attachment::whereIn('id', $attachmentIds)->get()->each(function (Attachment $attachment) use ($model, $type) {
            $attachment->attachmentable()->associate($model);
            $attachment->type = $type;
            $attachment->save();
        });
    }

    protected function resolveAttachmentIds(Request $request, string $key): ?array
    {
        $value = $request->input($key);

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return array_filter(array_map('trim', explode(',', $value)));
        }

        return null;
    }
}
