<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Model;


abstract class EntityRepository extends \Dogma\Object
{

    /** @var \Dogma\Database\Connection */
    private $db;

    /** @var string */
    protected $table;


    /**
     * @param \Dogma\Database\Connection
     */
    public function __construct(\Dogma\Database\Connection $db)
    {
        $this->db = $db;
    }


    /**
     * @param int|string
     */
    public function get($id)
    {
        return $this->db->table($this->table)->get($id);
    }


    public function getEntityClass(\Nette\Database\Table\ActiveRow $row)
    {
        ///
    }

}
