<?php

include('db.inc.php');
$DB_NAME = "dbname";

function getUniqueCalls($params = array())
{
	global $db;

	$sql = "select distinct(COL_CALL) from TABLE_HRD_CONTACTS_V01";

	if($params)
	{
		$sql .= " WHERE ";

		$firstCond = 1;
		# Step through parameters and build the query
		foreach ($params as $k => $v)
		{
			if($firstCond != 1)
			{
				$sql .= " AND ";
			}
			else
			{
				$firstCond = 0;
			}
			$sql .= $k . " LIKE '" . $v . "'";
		}
	}

	$rs = $db->Execute($sql);

	if($rs->_numOfRows > 0)
	{
		$UniqueCalls = $rs->GetArray();
		return $UniqueCalls;
	}
	else
	{
		return -1;
	}
}

function countQSO()
{
	global $db;

	$sql = "select * from TABLE_HRD_CONTACTS_V01";

	$rs = $db->Execute($sql);

	return $rs->_numOfRows;
}

function getQSOList($params = array(), $orderBy = "COL_TIME_ON", $orderDir = "DESC", $limit = null)
{
	global $db;

	$sql = "select * from TABLE_HRD_CONTACTS_V01";
	$csql = "SELECT COUNT(*) AS RECORD_COUNT FROM TABLE_HRD_CONTACTS_V01";

	if($params)
	{
		$sql .= " WHERE ";
		$csql .= " WHERE ";

		$firstCond = 1;
		# Step through parameters and build the query
		foreach ($params as $val)
	#	$k => $v)
		{
			if($firstCond != 1)
			{
				$sql .= " AND ";
				$csql .= " AND ";
			}
			else
			{
				$firstCond = 0;
			}
			$sql .= key($val) . " LIKE '%" . $val[key($val)] . "%'";
			$csql .= key($val) . " LIKE '%" . $val[key($val)] . "%'";
		}
	}

	$sql .= " ORDER BY ".$orderBy." ".$orderDir;
	$csql .= " ORDER BY ".$orderBy." ".$orderDir;

	if($limit)
	{
		$sql .= " LIMIT ".$limit;
	}

	$rs = $db->Execute($sql);

	if($rs->_numOfRows > 0)
	{
		$QSOList['qso'] = $rs->GetArray();

		$crs = $db->Execute($csql);
		$c = $crs->FetchRow();
		$QSOList['count'] = $c['RECORD_COUNT'];

		return $QSOList;
	}
	else
	{
		return -1;
	}
}
