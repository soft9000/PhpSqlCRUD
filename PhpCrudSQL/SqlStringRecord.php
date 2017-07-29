<?php

/** Created 2017/07/29 - Happy Saturday.
 * 
 * For 100% of what we need to do at the moment (manage our data on 
 * our local network,) this class will do what we need it to do: CRUD a 
 * row consisting of TEXT / string values!
 * 
 * So, is DAO-generation necessary in Modern PHP? 
 * 
 * When using a name-associated / indexed array, all we need do is to 
 * define our fields, and away we go!
 * 
 * Hence, feel free to update:
 * 
 * get_columns()
 * get_table_name() [optional]
 * get_file_name()  [optional]
 * 
 * Then simply:
 * 
 * (1) Use associative arrays as your "data access object" (as demonstrated 
 * &amp; returned from the read($id) operation.)
 * 
 * (2) Access results via your own get_columns() names -
 * 
 * (3) to (ahem) see how easy it be to make CRUD happen!
 * 
 * Finally, if your are unfamiliar with how to use associative arrays, then 
 * see the test case (below) for a decent, demonstrative, example.
 * 
 * 
 * Sharing is Caring!
 * 
 * Randall Nagy
 * President, Soft9000.com
 * 
 * 
 */
class SqlStringRecord {

    function get_file_name() {
        return "default.sqlt3";
    }

    function get_table_name() {
        return "contacts";
    }

    function get_columns() {
        $data = array();
        $data[0] = "Name";
        $data[1] = "Address";
        $data[2] = "Note";
        return $data;
    }

    function delete_table() {
        $db = new SQLite3($this->get_file_name());
        $tbl = $this->get_table_name();
        $db->exec("DROP TABLE IF EXISTS $tbl");
        $db->close();
        if ($db->lastErrorCode() != 0) {
            return FALSE;
        }
        return TRUE;
    }

    function create_table() {
        $this->delete_table();
        $fields = $this->get_columns();
        $tbl = $this->get_table_name();
        $cmd = "CREATE TABLE $tbl (ID INTEGER PRIMARY KEY AUTOINCREMENT";
        foreach ($fields as $field) {
            $cmd .= ', ';
            $cmd .= $field;
            $cmd .= ' STRING';
        }
        $cmd .= ")";
        $db = new SQLite3($this->get_file_name());
        $db->exec($cmd);
        $db->close();
        if ($db->lastErrorCode() != 0) {
            return FALSE;
        }
        return TRUE;
    }

    function count() {
        $db = new SQLite3($this->get_file_name());
        $tbl = $this->get_table_name();
        $rs = $db->query("SELECT COUNT(*) FROM $tbl");
        if ($rs == FALSE) {
            return FALSE;
        }
        $data = $rs->fetchArray();
        $db->close();
        return $data[0];
    }

    function create($special) {
        $cols = $this->get_columns();
        $tbl = $this->get_table_name();
        $cmd = "INSERT INTO $tbl (";
        $bfirst = true;
        foreach ($cols as $col) {
            if ($bfirst == false) {
                $cmd .= ", ";
            }
            $cmd .= $col;
            $bfirst = false;
        }
        $cmd .= ") VALUES (";
        $bfirst = true;
        foreach ($cols as $col) {
            if ($bfirst == false) {
                $cmd .= ", ";
            }
            $cmd .= "'$special[$col]'";
            $bfirst = false;
        } $cmd .= ")";
        echo "\n\n$cmd\n\n";
        $db = new SQLite3($this->get_file_name());
        $db->exec($cmd);
        $db->close();
        if ($db->lastErrorCode() != 0) {
            return FALSE;
        }
        return $this->count();
    }

    function read($id) {
        $db = new SQLite3($this->get_file_name());
        $tbl = $this->get_table_name();
        $cmd = "SELECT * FROM $tbl WHERE ID = $id LIMIT 1";
        $rs = $db->query($cmd);
        if ($rs == FALSE) {
            return FALSE;
        }
        $results = $rs->fetchArray(SQLITE3_ASSOC);
        $db->close();
        return $results;
    }

    function update($id, $special) {
        $tbl = $this->get_table_name();
        $cmd = "UPDATE $tbl SET";
        $cols = $this->get_columns();
        $bfirst = TRUE;
        foreach ($cols as $col) {
            if ($bfirst == FALSE) {
                $cmd .= ", ";
            }
            $cmd .= " $col = '$special[$col]'";
            $bfirst = FALSE;
        }
        $cmd .= " WHERE ID = $id";
        echo "\n\n$cmd\n\n";
        $db = new SQLite3($this->get_file_name());
        $db->exec($cmd);
        $db->close();
        if ($db->lastErrorCode() != 0) {
            return FALSE;
        }
        return TRUE;
    }

    function delete($id) {
        $tbl = $this->get_table_name();
        $cmd = "DELETE FROM $tbl WHERE ID = $id";
        $db = new SQLite3($this->get_file_name());
        $db->exec($cmd);
        $db->close();
        if ($db->lastErrorCode() != 0) {
            return FALSE;
        }
        return TRUE;
    }

}

/*
 * THE BASIC TEST CASE
 */

$dao = new SqlStringRecord();
$dao->create_table();
if ($dao->create_table() == FALSE) {
    $table = $dao->get_table_name();
    echo "Unable to create table $table\n";
    exit(-1);
}

$special = array("Name" => "Randall Nagy", "Address" => "soft9000.com", "Note" => "The simple way to CRUD SQL");
$which = $dao->create($special);
if ($which == FALSE) {
    $table = $dao->get_table_name();
    echo "Unable to add data to $table\n";
    exit(-1);
}

$row = $dao->read($which);
if ($row == FALSE) {
    $table = $dao->get_table_name();
    echo "Unable to read data #$which from $table\n";
    exit(-1);
}

foreach ($dao->get_columns() as $col) {
    echo "$col: $row[$col]\n";
}

foreach ($dao->get_columns() as $col) {
    $row[$col] .= ", updated!";
}

if ($dao->update($which, $row) == FALSE) {
    $table = $dao->get_table_name();
    echo "Unable to UPDATE data #$which from $table\n";
    exit(-1);
}

foreach ($dao->get_columns() as $col) {
    echo "$col: $row[$col]\n";
}

if ($dao->count() != 1) {
    $table = $dao->get_table_name();
    echo "Unable to STAT data #$which from $table\n";
    exit(-1);
}

if ($dao->delete($which) == FALSE) {
    $table = $dao->get_table_name();
    echo "Unable to DELETE data #$which from $table\n";
    exit(-1);
}

if ($dao->count() != 0) {
    $table = $dao->get_table_name();
    echo "Unable to STAT data #$which from $table\n";
    exit(-1);
}

echo "\n\nTESTING SUCCESS!\n\n";

exit(0);
