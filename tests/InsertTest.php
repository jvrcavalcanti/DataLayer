<?php

use Accolon\DataLayer\Db;
use PHPUnit\Framework\TestCase;

class InsertTest extends TestCase
{
    public function testSave()
    {
        $db = DB::table('users');

        $db->username = "Test Create";
        $db->password = "123456";

        $result = $db->save();

        $this->assertTrue($result);

        if ($result) {
            $db->where(["username", "=", "Test Create"])->delete();
        }
    }

    public function testCreate()
    {
        $db = DB::table('users');

        $result = $db->create([
            "username" => "Test Create",
            "password" => "123456"
        ]);

        $this->assertTrue($result);

        if ($result) {
            $db->where(["username", "=", "Test Create"])->delete();
        }
    }
}