<?php

class WprMonitor {

	protected $start;
	protected $themepath;
	protected $filetypes;
	protected $deadLine;

	public function __construct()
	{
		if ( !isset( $_POST[ 'themepath' ] ) ||
			 !isset( $_POST[ 'filetypes' ] )
		) {
			header('HTTP/1.1 400 Bad Request');
			die();
		}

		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: -1' );

		clearstatcache();

		$this->themepath 	= $_POST[ 'themepath' ];
		$this->filetypes 	= $_POST[ 'filetypes' ];
		$this->files 		= array();
		$this->starttime	= time();
		$this->endtime 		= null;

		$this->setEndTime();

		$this->getFiles( $this->themepath );
		$this->checkFiles();
	}

	protected function setEndTime()
	{
		if ( !ini_get( 'safe_mode' ) )
		{
			set_time_limit( 125 );
		}

		$limit = ini_get( 'max_execution_time' );
		if ( empty( $limit ) || $limit < 1 )
		{
			$limit = 30;
		}

		$this->endtime = $this->starttime + $limit - 5;
	}

	protected function getFiles( $dir )
	{
		$files = glob( $dir . '/*' );
		if ( empty( $files ) || !is_array( $files ) )
		{
			return;
		}

		foreach ( $files as $file )
		{
			if ( is_dir( $file ) )
			{
				$this->getFiles( $file );
			}
			else if ( is_file( $file ) )
			{
				$pinfo = pathinfo( $file );
				if ( in_array( $pinfo[ 'extension' ], $this->filetypes ) )
				{
					$this->files[] = $file;
				}
			}
		}
	}

	protected function checkFiles()
	{
		foreach ( $this->files as $file )
		{
			$mtime = filemtime( $file );
			if ( $mtime && $this->starttime < $mtime )
			{
				die( $file );
			}
		}

		if ( time() < $this->endtime )
		{
			sleep( 1 );
			$this->checkFiles();
		}
		else
		{
			die( 'rerun' );
		}
	}

}

new WprMonitor();