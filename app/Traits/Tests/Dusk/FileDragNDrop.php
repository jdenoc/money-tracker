<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\StorageTestFiles;
use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait FileDragNDrop {
    use StorageTestFiles;
    use WaitTimes;

    private static $SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL = ".dz-complete:last-child";
    private static $SELECTOR_FILE_DRAG_N_DROP__DROPZONE_PROGRESS = ".dz-progress";
    private static $SELECTOR_FILE_DRAG_N_DROP__DROPZONE_ERROR_MARK = ".dz-error-mark";
    private static $SELECTOR_FILE_DRAG_N_DROP__DROPZONE_SUCCESS_MARK = ".dz-success-mark";
    private static $SELECTOR_FILE_DRAG_N_DROP__DROPZONE_LABEL_FILENAME = '.dz-filename';
    private static $SELECTOR_FILE_DRAG_N_DROP__DROPZONE_ERROR_MESSAGE = ".dz-error-message";
    private static $SELECTOR_FILE_DRAG_N_DROP__DROPZONE_BTN_REMOVE = ".dz-remove";

    private static $LABEL_FILE_DRAG_N_DROP = "Drag & Drop";
    private static $LABEL_FILE_DRAG_N_DROP__DROPZONE_REMOVE_FILE = "REMOVE FILE";
    private static $LABEL_FILE_UPLOAD_SUCCESS_NOTIFICATION = 'Uploaded: %s';
    private static $LABEL_FILE_UPLOAD_FAILURE_NOTIFICATION = '"File upload failure: %s';

    protected function assertDragNDropDefaultState(Browser $modal, string $drag_n_drop_selector) {
        $modal
            ->assertVisible($drag_n_drop_selector)
            ->assertSeeIn($drag_n_drop_selector, self::$LABEL_FILE_DRAG_N_DROP)
            ->assertMissing(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL);
    }

    protected function uploadAttachmentUsingDragNDropAndSuccess(Browser $modal, string $drag_n_drop_selector, string $hidden_input_selector, string $upload_file_path) {
        $this->uploadAttachmentUsingDragNDrop($modal, $drag_n_drop_selector, $hidden_input_selector, $upload_file_path);
        $modal->within($drag_n_drop_selector.' '.self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL, function(Browser $thumbnail) use ($upload_file_path) {
            $this->assertUploadSuccess($thumbnail, basename($upload_file_path));
        });
    }

    protected function uploadAttachmentUsingDragNDropAndFailure(Browser $modal, string $drag_n_drop_selector, string $hidden_input_selector, string $upload_file_path, $error_message) {
        $this->uploadAttachmentUsingDragNDrop($modal, $drag_n_drop_selector, $hidden_input_selector, $upload_file_path);
        $modal->within($drag_n_drop_selector.' '.self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL, function(Browser $thumbnail) use ($upload_file_path, $error_message) {
            $this->assertUploadFailure($thumbnail, basename($upload_file_path), $error_message);
        });
    }

    /**
     * @param Browser $modal
     * @param string  $drag_n_drop_selector
     * @param string  $hidden_input
     * @param string  $upload_file_path
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    private function uploadAttachmentUsingDragNDrop(Browser $modal, string $drag_n_drop_selector, string $hidden_input, string $upload_file_path) {
        $this->assertFileExists($upload_file_path);

        $modal
            ->assertVisible($drag_n_drop_selector)
            // upload file
            ->attach($hidden_input, $upload_file_path)
            // wait for thumbnail to appear
            ->waitFor(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL, static::$WAIT_SECONDS)
            ->within(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL, function(Browser $upload_thumbnail) use ($upload_file_path) {
                $upload_thumbnail->waitUntilMissing(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_PROGRESS, static::$WAIT_SECONDS);
            });
    }

    /**
     * @param Browser $upload_thumbnail
     * @param string  $upload_filename
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    private function assertUploadSuccess(Browser $upload_thumbnail, string $upload_filename) {
        $upload_thumbnail
            ->assertMissing(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_ERROR_MARK)
            ->mouseover('')    // hover over current element
            ->waitUntilMissing(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_SUCCESS_MARK, static::$WAIT_SECONDS)
            // confirm file name in upload node
            ->assertSeeIn(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_LABEL_FILENAME, $upload_filename)
            ->assertMissing(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_ERROR_MESSAGE);
    }

    /**
     * @param Browser $upload_thumbnail
     * @param string  $upload_filename
     * @param string  $error_message
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    private function assertUploadFailure(Browser $upload_thumbnail, string $upload_filename, string $error_message) {
        $upload_thumbnail
            ->assertVisible(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_ERROR_MARK)
            ->assertSeeIn(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_LABEL_FILENAME, $upload_filename)
            ->mouseover('')    // hover over current element
            ->waitFor(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_ERROR_MESSAGE, static::$WAIT_SECONDS)
            ->assertSeeIn(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_ERROR_MESSAGE, $error_message);
    }

    /**
     * @param Browser $modal
     */
    protected function removeUploadedAttachmentFromDragNDrop(Browser $modal, string $drag_n_drop_selector) {
        $modal
            ->within($drag_n_drop_selector.' '.self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL, function(Browser $upload_thumbnail) {
                // this will process is only valid on a successful upload
                $upload_thumbnail
                    ->mouseover("") // hover over current element
                    ->assertVisible(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_BTN_REMOVE)
                    ->assertSeeIn(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_BTN_REMOVE, self::$LABEL_FILE_DRAG_N_DROP__DROPZONE_REMOVE_FILE)
                    ->click(self::$SELECTOR_FILE_DRAG_N_DROP__DROPZONE_BTN_REMOVE);
            })
            ->pause(self::$WAIT_HALF_SECOND_IN_MILLISECONDS)   // give the element some time to disappear
            ->assertMissing(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_THUMBNAIL);
    }

}
