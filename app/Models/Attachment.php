<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Attachment extends BaseModel {
    use HasFactory;

    // storage locations
    const STORAGE_TMP_UPLOAD = 'tmp-uploads';
    const STORAGE_ATTACHMENTS = 'attachments';

    // variables
    protected $table = 'attachments';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';      // The "type" of the primary key ID.
    public $incrementing = false;   // because attachments.uuid isn't an int and so can't be incremented
    protected $fillable = [
        'uuid', 'entry_id', 'name',
    ];
    protected $guarded = [
        'stamp',
    ];
    protected $casts = [
        'stamp' => 'datetime',
    ];
    public $timestamps = false;

    public function entry() {
        return $this->belongsTo(Entry::class);
    }

    /**
     * Returns attachment filename extension
     * @return string
     */
    public function get_filename_extension() {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    /**
     * Return a hashed version of the attachment filename
     * @return string
     */
    public function get_hashed_filename() {
        $ext = $this->get_filename_extension();
        return sha1($this->name.$this->uuid).'.'.$ext;
    }

    /**
     * @return string
     */
    public function get_tmp_filename() {
        return $this->uuid.'.'.str_replace(' ', '_', $this->name);
    }

    /**
     * @param $file_contents
     * @param bool $is_tmp
     * @return bool
     */
    public function storage_store($file_contents, bool $is_tmp = false) {
        $storage_filename = $is_tmp ? $this->get_tmp_file_path() : $this->get_storage_file_path();
        return Storage::put($storage_filename, $file_contents);
    }

    /**
     * @param bool $is_tmp
     * @return bool
     */
    public function storage_exists(bool $is_tmp = false) {
        $storage_filename = $is_tmp ? $this->get_tmp_file_path() : $this->get_storage_file_path();
        return Storage::exists($storage_filename);
    }

    /**
     * @param bool $is_tmp
     * @return bool
     */
    public function storage_delete(bool $is_tmp = false) {
        $storage_filename = $is_tmp ? $this->get_tmp_file_path() : $this->get_storage_file_path();
        return Storage::delete($storage_filename);
    }

    public function storage_move_from_tmp_to_main() {
        if ($this->storage_exists(true) && !$this->storage_exists()) {
            Storage::move($this->get_tmp_file_path(), $this->get_storage_file_path());
        }
    }

    /**
     * @return string
     */
    public function get_tmp_file_path() {
        return self::STORAGE_TMP_UPLOAD.DIRECTORY_SEPARATOR.$this->get_tmp_filename();
    }

    /**
     * @return string
     */
    public function get_storage_file_path() {
        return self::STORAGE_ATTACHMENTS.DIRECTORY_SEPARATOR.$this->get_hashed_filename();
    }

}
