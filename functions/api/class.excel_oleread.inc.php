<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 4.0.0 Build 8059
|| # -------------------------------------------------------------------- # ||
|| # Customer License # H7LhzCqPSNEABnY
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2014 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
define('NUM_BIG_BLOCK_DEPOT_BLOCKS_POS', 0x2c);
define('SMALL_BLOCK_DEPOT_BLOCK_POS', 0x3c);
define('ROOT_START_BLOCK_POS', 0x30);
define('BIG_BLOCK_SIZE', 0x200);
define('SMALL_BLOCK_SIZE', 0x40);
define('EXTENSION_BLOCK_POS', 0x44);
define('NUM_EXTENSION_BLOCK_POS', 0x48);
define('PROPERTY_STORAGE_BLOCK_SIZE', 0x80);
define('BIG_BLOCK_DEPOT_BLOCKS_POS', 0x4c);
define('SMALL_BLOCK_THRESHOLD', 0x1000);
define('SIZE_OF_NAME_POS', 0x40);
define('TYPE_POS', 0x42);
define('START_BLOCK_POS', 0x74);
define('SIZE_POS', 0x78);
define('IDENTIFIER_OLE', pack("CCCCCCCC", 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1));

/**
* Class to read excel documents
*
* @package	iLance\Excel\Oleread
* @version      4.0.0.8059
* @author	ILance
*/
class excel_oleread
{
	var $data = '';
	function excel_oleread()
	{
	}
    
	function GetInt4d($data, $pos)
	{
		$value = ord($data[$pos]) | (ord($data[$pos+1])	<< 8) | (ord($data[$pos+2]) << 16) | (ord($data[$pos+3]) << 24);
		if ($value >= 4294967294)
		{
			$value=-2;
		}
		return $value;
	}
	function read($sFileName)
	{
		if (!is_readable($sFileName))
		{
			$this->error = 1;
			return false;
		}
		$this->data = @file_get_contents($sFileName);
		if (!$this->data)
		{ 
			$this->error = 1; 
			return false; 
		}
		if (substr($this->data, 0, 8) != IDENTIFIER_OLE)
		{
			$this->error = 1; 
			return false; 
		}
		$this->numBigBlockDepotBlocks = $this->GetInt4d($this->data, NUM_BIG_BLOCK_DEPOT_BLOCKS_POS);
		$this->sbdStartBlock = $this->GetInt4d($this->data, SMALL_BLOCK_DEPOT_BLOCK_POS);
		$this->rootStartBlock = $this->GetInt4d($this->data, ROOT_START_BLOCK_POS);
		$this->extensionBlock = $this->GetInt4d($this->data, EXTENSION_BLOCK_POS);
		$this->numExtensionBlocks = $this->GetInt4d($this->data, NUM_EXTENSION_BLOCK_POS);
		$bigBlockDepotBlocks = array();
		$pos = BIG_BLOCK_DEPOT_BLOCKS_POS;
		$bbdBlocks = $this->numBigBlockDepotBlocks;
		if ($this->numExtensionBlocks != 0)
		{
			$bbdBlocks = (BIG_BLOCK_SIZE - BIG_BLOCK_DEPOT_BLOCKS_POS)/4; 
		}
		for ($i = 0; $i < $bbdBlocks; $i++)
		{
			$bigBlockDepotBlocks[$i] = $this->GetInt4d($this->data, $pos);
			$pos += 4;
		}
		for ($j = 0; $j < $this->numExtensionBlocks; $j++)
		{
			$pos = ($this->extensionBlock + 1) * BIG_BLOCK_SIZE;
			$blocksToRead = min($this->numBigBlockDepotBlocks - $bbdBlocks, BIG_BLOCK_SIZE / 4 - 1);
			for ($i = $bbdBlocks; $i < $bbdBlocks + $blocksToRead; $i++)
			{
				$bigBlockDepotBlocks[$i] = $this->GetInt4d($this->data, $pos);
				$pos += 4;
			}   
			$bbdBlocks += $blocksToRead;
			if ($bbdBlocks < $this->numBigBlockDepotBlocks)
			{
				$this->extensionBlock = $this->GetInt4d($this->data, $pos);
			}
		}
		// readBigBlockDepot
		$pos = 0;
		$index = 0;
		$this->bigBlockChain = array();
		for ($i = 0; $i < $this->numBigBlockDepotBlocks; $i++)
		{
			$pos = ($bigBlockDepotBlocks[$i] + 1) * BIG_BLOCK_SIZE;
			//echo "pos = $pos";	
			for ($j = 0 ; $j < BIG_BLOCK_SIZE / 4; $j++)
			{
				$this->bigBlockChain[$index] = $this->GetInt4d($this->data, $pos);
				$pos += 4 ;
				$index++;
			}
		}
		$pos = 0;
		$index = 0;
		$sbdBlock = $this->sbdStartBlock;
		$this->smallBlockChain = array();
		while ($sbdBlock != -2)
		{
			$pos = ($sbdBlock + 1) * BIG_BLOCK_SIZE;
			for ($j = 0; $j < BIG_BLOCK_SIZE / 4; $j++)
			{
				$this->smallBlockChain[$index] = $this->GetInt4d($this->data, $pos);
				$pos += 4;
				$index++;
			}
			$sbdBlock = $this->bigBlockChain[$sbdBlock];
		}
		// readData(rootStartBlock)
		$block = $this->rootStartBlock;
		$pos = 0;
		$this->entry = $this->__readData($block);
		$this->__readPropertySets();
	}
	function __readData($bl)
	{
		$block = $bl;
		$pos = 0;
		$data = '';
		while ($block != -2)
		{
			$pos = ($block + 1) * BIG_BLOCK_SIZE;
			$data = $data.substr($this->data, $pos, BIG_BLOCK_SIZE);
			//echo "pos = $pos data=$data\n";	
			$block = $this->bigBlockChain[$block];
		}
		return $data;
	}
	function __readPropertySets()
	{
		$offset = 0;
		while ($offset < strlen($this->entry))
		{
			$d = substr($this->entry, $offset, PROPERTY_STORAGE_BLOCK_SIZE);
			$nameSize = ord($d[SIZE_OF_NAME_POS]) | (ord($d[SIZE_OF_NAME_POS+1]) << 8);
			$type = ord($d[TYPE_POS]);
			$startBlock = $this->GetInt4d($d, START_BLOCK_POS);
			$size = $this->GetInt4d($d, SIZE_POS);
			$name = '';
			for ($i = 0; $i < $nameSize ; $i++)
			{
			    $name .= $d[$i];
			}
			$name = str_replace("\x00", "", $name);
			$this->props[] = array (
				'name' => $name, 
				'type' => $type,
				'startBlock' => $startBlock,
				'size' => $size);
			if (($name == "Workbook") || ($name == "Book"))
			{
				$this->wrkbook = count($this->props) - 1;
			}
			if ($name == "Root Entry")
			{
				$this->rootentry = count($this->props) - 1;
			}
			$offset += PROPERTY_STORAGE_BLOCK_SIZE;
		}   
	}
    
	function getWorkBook()
	{
		if ($this->props[$this->wrkbook]['size'] < SMALL_BLOCK_THRESHOLD)
		{
			$rootdata = $this->__readData($this->props[$this->rootentry]['startBlock']);
			$streamData = '';
			$block = $this->props[$this->wrkbook]['startBlock'];
			$pos = 0;
			while ($block != -2)
			{
				$pos = $block * SMALL_BLOCK_SIZE;
				$streamData .= substr($rootdata, $pos, SMALL_BLOCK_SIZE);
				$block = $this->smallBlockChain[$block];
			}
			return $streamData;
		}
		else
		{
			$numBlocks = $this->props[$this->wrkbook]['size'] / BIG_BLOCK_SIZE;
			if ($this->props[$this->wrkbook]['size'] % BIG_BLOCK_SIZE != 0)
			{
			    $numBlocks++;
			}
			if ($numBlocks == 0) return '';
			$streamData = '';
			$block = $this->props[$this->wrkbook]['startBlock'];
			$pos = 0;
			while ($block != -2)
			{
				$pos = ($block + 1) * BIG_BLOCK_SIZE;
				$streamData .= substr($this->data, $pos, BIG_BLOCK_SIZE);
				$block = $this->bigBlockChain[$block];
			}   
			return $streamData;
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>