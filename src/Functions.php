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

	public static function anyset(...$vars) : Bool {
		// http://stackoverflow.com/a/40496372/4233593
		foreach($vars as $var){
			if(isset($var)){
				return true;
			}
		}
		return false;
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
		$dir = dirname($filename);
		if(!is_dir($dir)){
			if(!mkdir($dir, 0777, true)){
				throw new Exception("Could not create directory $dir");
			}
		}

		file_put_contents($filename, $data, $flags, $context);
	}

	public static function FriendlyErrorType(Int $type) : String {
		// http://php.net/manual/en/errorfunc.constants.php#109430
		switch($type){
			case E_ERROR: // 1 //
				return 'E_ERROR';
			case E_WARNING: // 2 //
				return 'E_WARNING';
			case E_PARSE: // 4 //
				return 'E_PARSE';
			case E_NOTICE: // 8 //
				return 'E_NOTICE';
			case E_CORE_ERROR: // 16 //
				return 'E_CORE_ERROR';
			case E_CORE_WARNING: // 32 //
				return 'E_CORE_WARNING';
			case E_COMPILE_ERROR: // 64 //
				return 'E_COMPILE_ERROR';
			case E_COMPILE_WARNING: // 128 //
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR: // 256 //
				return 'E_USER_ERROR';
			case E_USER_WARNING: // 512 //
				return 'E_USER_WARNING';
			case E_USER_NOTICE: // 1024 //
				return 'E_USER_NOTICE';
			case E_STRICT: // 2048 //
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR: // 4096 //
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED: // 8192 //
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED: // 16384 //
				return 'E_USER_DEPRECATED';
		}
		return "";
	}

	public static function ErrorsToExceptions(){
		set_error_handler(function($errno, $errstr, $errfile, $errline){
			throw new Exception(static::FriendlyErrorType($errno).
				" line $errline in $errfile: $errstr\n", $errno
			);
		});
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
