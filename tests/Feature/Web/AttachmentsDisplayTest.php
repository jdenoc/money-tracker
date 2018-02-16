<?php

namespace Tests\Feature\Web;

use App\Attachment;
use Faker\Factory as FakerFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Ramsey\Uuid\Uuid;

class AttachmentsDisplayTest extends TestCase {

    const WEB_ATTACHMENT_URI = "/attachment/%s";
    const ATTACHMENT_PARAMETER_UUID = 'uuid';
    const ATTACHMENT_PARAMETER_FILENAME = 'name';

    /**
     * @var \Faker\Generator;
     */
    private $_faker;

    /**
     * @var string[]
     */
    private $_valid_file_types = [
        'pdf',
        'jpeg',
        'jpg',
        'png',
        'gif',
        'txt',
    ];

    public function setUp(){
        parent::setUp();
        $this->_faker = FakerFactory::create();
    }

    public function testInvalidUuid(){
        // GIVEN
        $invalid_uuid = $this->_faker->word;   // a word will never be a valid UUID

        // WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $invalid_uuid));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
    }

    public function testNoDatabaseRecord(){
        //GIVEN - no attachment database record
        $uuid = $this->generateValidUuid();

        //WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $uuid));

        //THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
    }

    public function testFileNotOnDisk(){
        //GIVEN - file does NOT exist on disk
        $generated_attachment = factory(Attachment::class)->create([self::ATTACHMENT_PARAMETER_UUID=>$this->generateValidUuid()]);

        //WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $generated_attachment->uuid));

        //THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
    }

    public function testInvalidAttachmentFileType(){
        //GIVEN
        do{
            // file extension should NOT be in the approved list
            $file_ext = $this->_faker->fileExtension;
        }while(in_array($file_ext, $this->_valid_file_types));
        $generated_attachment = factory(Attachment::class)->create([
            self::ATTACHMENT_PARAMETER_UUID => $this->generateValidUuid(),
            self::ATTACHMENT_PARAMETER_FILENAME => $this->_faker->word.'.'.$file_ext
        ]);
        // make sure file exists on disk by generating a dummy file.
        $generated_attachment->storage_store($this->_faker->sentence);

        //WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $generated_attachment->uuid));

        //THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    public function testViewingAValidAttachment(){
        //GIVEN
        $generated_attachment = factory(Attachment::class)->create([
            self::ATTACHMENT_PARAMETER_UUID => $this->generateValidUuid(),
            self::ATTACHMENT_PARAMETER_FILENAME => $this->_faker->word.'.'.$this->_faker->randomElement($this->_valid_file_types)
        ]);
        // make sure file exists on disk by generating a dummy file.
        $generated_attachment->storage_store($this->_faker->sentence);

        //WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $generated_attachment->uuid));

        //THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK);
    }

    private function generateValidUuid(){
        do{
            $valid_uuid = $this->_faker->uuid;
        }while(!Uuid::isValid($valid_uuid));
        return $valid_uuid;
    }

}