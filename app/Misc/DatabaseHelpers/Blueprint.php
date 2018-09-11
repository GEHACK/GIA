<?php namespace App\Misc\DatabaseHelpers;

class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    public function timestamp($column, $precision = 0) {
        return $this->dateTime($column);
    }

    public function timestampTz($column, $precison = 0) {
        return $this->dateTime($column);
    }

    public function dateTime($column, $default = false) {
        $a = $this->char($column, 32);
        return ($default === false || !is_string($default) ? $a : $a->default($default));
    }

    public function guid($name = 'guid', $primary = false){
        $a = $this->addColumn('uuid', $name);
        return ($name === 'guid' || $primary !== false ? $a->primary() : $a);
    }

    public function reorder($orderable, $key = 'guid', $column = 'preceded_by') {
        $a = $this->guid($column)->nullable();
        $this->foreign($column)->references($key)->on($orderable)->onDelete('set null');
        return $a;
    }

    public function dropReorder($column = 'preceded_by'){
        $this->dropForeign($this->table ."_". $column . "_foreign");
        $this->dropColumn($column);
    }
}
