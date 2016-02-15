<?php
/**
 * @package memberspamcheck
 */

/**
 * Necessary because {@link Object::create_from_string} doesn't allow negative defaults...
 */
class MemberSpamCheck_Int extends Int
{
    public function requireField()
    {
        $parts=array(
            'datatype'=>'int',
            'precision'=>11,
            'null'=>'not null',
            'default'=> -1,
            'arrayValue'=>$this->arrayValue
        );
        $values=array('type'=>'int', 'parts'=>$parts);
        DB::requireField($this->tableName, $this->name, $values);
    }
}
