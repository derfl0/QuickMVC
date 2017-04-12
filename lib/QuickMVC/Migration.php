<?php

namespace QuickMVC;


interface Migration
{
    public static function up();

    public static function down();
}