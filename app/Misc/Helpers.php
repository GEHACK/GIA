<?php

class Helpers {

    public static function getKey($isPublic = true, $contents = true) {
        $keyLoc = storage_path('ssh/');
        if (!file_exists($keyLoc))
            mkdir($keyLoc);

        $keyLoc .= "key";
        if (!file_exists($keyLoc))
            `ssh-keygen -b 2048 -t rsa -f $keyLoc -q`;

        if ($isPublic)
            $keyLoc .= ".pub";

        if (!$contents)
            return $keyLoc;

        return file_get_contents($keyLoc);
    }
}
