<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: $
 */

/**
 * A single criteria

 */
class Xmf_Database_Criteria extends Criteria
{
    /**
     * Make a sql condition string
     *
     * @return string
     */
    public function render()
    {
        $clause = (!empty($this->prefix) ? "{$this->prefix}." : "") . $this->column;
        if (!empty($this->function)) {
            $clause = sprintf($this->function, $clause);
        }
        if (in_array(strtoupper($this->operator), array('IS NULL', 'IS NOT NULL'))) {
            $clause .= ' ' . $this->operator;
        } else {
            //Allow empty values
            $value = trim($this->value);

            if (!in_array(strtoupper($this->operator), array('IN', 'NOT IN'))) {
                if ((substr($value, 0, 1) != '`') && (substr($value, -1) != '`')) {
                    $value = "'{$value}'";
                } else {
                    if (!preg_match('/^[a-zA-Z0-9_\.\-`]*$/', $value)) {
                        $value = '``';
                    }
                }
            }
            $clause .= " {$this->operator} {$value}";
        }
        return $clause;
    }
}
