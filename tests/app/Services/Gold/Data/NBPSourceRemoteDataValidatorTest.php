<?php
namespace App\Services\Gold\Data;

use App\Services\Gold\NBPGoldSource;

class NBPSourceRemoteDataValidatorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var
     */
    public $object;

    public function setUp(){
        $this->object = new NBPGoldSource();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to fetch date from NBP
     */
    public function testValidateResponseWithEmptyResult() {
        $this->object->validateResponse('');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Unable to parse date from NBP with message.+/
     */
    public function testValidateResponseWithInvalidJson() {
        $this->object->validateResponse('{aaaa');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Invalid data+/
     * @dataProvider validateResponseWithInvalidDataDataProvider
     */
    public function testValidateResponseWithInvalidData($value){
        $data = [['data'=>date('Y-m-d'), 'cena'=>$value]];
        $this->object->validateResponse(json_encode($data));
    }

    /**
     * @return array
     */
    public function validateResponseWithInvalidDataDataProvider() {
        return [
            ['aaaaa'],
            [''],
            ['aaa1'],
            ['0'],
        ];
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Invalid date+/
     * @dataProvider validateResponseWithInvalidDateDataProvider
     */
    public function testValidateResponseWithInvalidDate($value){
        $data = [['data'=>$value, 'cena'=>10]];
        $this->object->validateResponse(json_encode($data));
    }

    /**
     * @return array
     */
    public function validateResponseWithInvalidDateDataProvider() {
        return [
            ['aaaaa'],
            [''],
            ['aaa1'],
            ['0'],
            [date('Y-02-31')]
        ];
    }
}
