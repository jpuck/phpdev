<?php
namespace jpuck\phpdev;

use Exception;

class Functions {
	public static function strbegins(String $haystack, String $needle) : Bool {
		// http://stackoverflow.com/a/7168986/4233593
		return $haystack[0] === $needle[0]
			? strncmp($haystack, $needle, strlen($needle)) === 0
			: false;
	}

	public static function print_rt($array, Bool $return = false){
		$tabulated = str_replace('        ', '	', print_r($array, true));
		if($return){
			return $tabulated;
		}
		echo $tabulated;
	}

	public static function arr_export(Array $array, Bool $return = false){
		$result = var_export($array, true);

		// replace 2-space indentations with a tab
		// http://stackoverflow.com/a/39682092/4233593
		$result = preg_replace ( '~(?:^|\G)\h{2}~m',  "\t",     $result);

		// open array
		$result = str_replace  ( "array (\n",         "[\n",    $result);
		// close array
		$result = str_replace  ( "\t),\n",            "\t],\n", $result);
		// close final array
		$result = preg_replace ( "/\)$/",             "]",      $result);

		// arrows without trailing spaces
		$result = str_replace  ( "=> \n",             "=>\n",   $result);

		if($return){
			return $result;
		}
		echo $result;
	}

	// creates parent directories if they don't exist
	public static function file_put_contents(string $filename, $data, int $flags = 0, $context = null){
		$mkdirp = function(string $dir) use (&$mkdirp){
			if(file_exists($dir)){
				// make sure this file is a directory
				if(!is_dir($dir)){
					throw new Exception("$dir exists, but is not a directory.");
				}
			}elseif($mkdirp(dirname($dir))){
				mkdir($dir);
			}
			return true;
		};

		$mkdirp(dirname($filename));

		file_put_contents($filename, $data, $flags, $context);
	}

	// dangerously powerful
	// http://stackoverflow.com/a/1473313/4233593
	public static function CleanMsSQLdb(\PDO $pdo){
		$sql = "
			/* Drop all non-system stored procs */
			DECLARE @name VARCHAR(128)
			DECLARE @SQL VARCHAR(254)

			SELECT @name = (SELECT TOP 1 [name] FROM sysobjects WHERE [type] = 'P' AND category = 0 ORDER BY [name])

			WHILE @name is not null
			BEGIN
				SELECT @SQL = 'DROP PROCEDURE [dbo].[' + RTRIM(@name) +']'
				EXEC (@SQL)
				SELECT @name = (SELECT TOP 1 [name] FROM sysobjects WHERE [type] = 'P' AND category = 0 AND [name] > @name ORDER BY [name])
			END
		";
		$pdo->query($sql)->closeCursor();

		$sql = "
			/* Drop all views */
			DECLARE @name VARCHAR(128)
			DECLARE @SQL VARCHAR(254)

			SELECT @name = (SELECT TOP 1 [name] FROM sysobjects WHERE [type] = 'V' AND category = 0 ORDER BY [name])

			WHILE @name IS NOT NULL
			BEGIN
				SELECT @SQL = 'DROP VIEW [dbo].[' + RTRIM(@name) +']'
				EXEC (@SQL)
				SELECT @name = (SELECT TOP 1 [name] FROM sysobjects WHERE [type] = 'V' AND category = 0 AND [name] > @name ORDER BY [name])
			END
		";
		$pdo->query($sql)->closeCursor();

		$sql = "
			/* Drop all functions */
			DECLARE @name VARCHAR(128)
			DECLARE @SQL VARCHAR(254)

			SELECT @name = (SELECT TOP 1 [name] FROM sysobjects WHERE [type] IN (N'FN', N'IF', N'TF', N'FS', N'FT') AND category = 0 ORDER BY [name])

			WHILE @name IS NOT NULL
			BEGIN
				SELECT @SQL = 'DROP FUNCTION [dbo].[' + RTRIM(@name) +']'
				EXEC (@SQL)
				SELECT @name = (SELECT TOP 1 [name] FROM sysobjects WHERE [type] IN (N'FN', N'IF', N'TF', N'FS', N'FT') AND category = 0 AND [name] > @name ORDER BY [name])
			END
		";
		$pdo->query($sql)->closeCursor();

		$sql = "
			/* Drop all Foreign Key constraints */
			DECLARE @name VARCHAR(128)
			DECLARE @constraint VARCHAR(254)
			DECLARE @SQL VARCHAR(254)

			SELECT @name = (SELECT TOP 1 TABLE_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE constraint_catalog=DB_NAME() AND CONSTRAINT_TYPE = 'FOREIGN KEY' ORDER BY TABLE_NAME)

			WHILE @name is not null
			BEGIN
				SELECT @constraint = (SELECT TOP 1 CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE constraint_catalog=DB_NAME() AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND TABLE_NAME = @name ORDER BY CONSTRAINT_NAME)
				WHILE @constraint IS NOT NULL
				BEGIN
					SELECT @SQL = 'ALTER TABLE [dbo].[' + RTRIM(@name) +'] DROP CONSTRAINT [' + RTRIM(@constraint) +']'
					EXEC (@SQL)
					SELECT @constraint = (SELECT TOP 1 CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE constraint_catalog=DB_NAME() AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME <> @constraint AND TABLE_NAME = @name ORDER BY CONSTRAINT_NAME)
				END
			SELECT @name = (SELECT TOP 1 TABLE_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE constraint_catalog=DB_NAME() AND CONSTRAINT_TYPE = 'FOREIGN KEY' ORDER BY TABLE_NAME)
			END
		";
		$pdo->query($sql)->closeCursor();

		$sql = "
			/* Drop all Primary Key constraints */
			DECLARE @name VARCHAR(128)
			DECLARE @constraint VARCHAR(254)
			DECLARE @SQL VARCHAR(254)

			SELECT @name = (SELECT TOP 1 TABLE_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE constraint_catalog=DB_NAME() AND CONSTRAINT_TYPE = 'PRIMARY KEY' ORDER BY TABLE_NAME)

			WHILE @name IS NOT NULL
			BEGIN
				SELECT @constraint = (SELECT TOP 1 CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE constraint_catalog=DB_NAME() AND CONSTRAINT_TYPE = 'PRIMARY KEY' AND TABLE_NAME = @name ORDER BY CONSTRAINT_NAME)
				WHILE @constraint is not null
				BEGIN
					SELECT @SQL = 'ALTER TABLE [dbo].[' + RTRIM(@name) +'] DROP CONSTRAINT [' + RTRIM(@constraint)+']'
					EXEC (@SQL)
					SELECT @constraint = (SELECT TOP 1 CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE constraint_catalog=DB_NAME() AND CONSTRAINT_TYPE = 'PRIMARY KEY' AND CONSTRAINT_NAME <> @constraint AND TABLE_NAME = @name ORDER BY CONSTRAINT_NAME)
				END
			SELECT @name = (SELECT TOP 1 TABLE_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE constraint_catalog=DB_NAME() AND CONSTRAINT_TYPE = 'PRIMARY KEY' ORDER BY TABLE_NAME)
			END
		";
		$pdo->query($sql)->closeCursor();

		$sql = "
			/* Drop all tables */
			DECLARE @name VARCHAR(128)
			DECLARE @SQL VARCHAR(254)

			SELECT @name = (SELECT TOP 1 [name] FROM sysobjects WHERE [type] = 'U' AND category = 0 ORDER BY [name])

			WHILE @name IS NOT NULL
			BEGIN
				SELECT @SQL = 'DROP TABLE [dbo].[' + RTRIM(@name) +']'
				EXEC (@SQL)
				SELECT @name = (SELECT TOP 1 [name] FROM sysobjects WHERE [type] = 'U' AND category = 0 AND [name] > @name ORDER BY [name])
			END
		";
		$pdo->query($sql)->closeCursor();
	}
}
