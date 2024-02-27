<?php

namespace Babardev\JunkFilesScanner\Helpers;

class FileHelper
{

    /**
     * Read File
     *
     * Opens the file specified in the path and returns it as a string.
     * @param	string	$file	Path to file
     * @return	string	File contents
     */
    public static function read_file($file)
    {
        return @file_get_contents($file);
    }

    /**
     * Write File
     *
     * Writes data to the file specified in the path.
     * Creates a new file if non-existent.
     *
     * @param	string	$path	File path
     * @param	string	$data	Data to write
     * @param	string	$mode	fopen() mode (default: 'wb')
     * @return	bool
     */
    public static function write_file($path, $data, $mode = 'wb')
    {
        if ( ! $fp = @fopen($path, $mode))
        {
            return FALSE;
        }

        flock($fp, LOCK_EX);

        for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result)
        {
            if (($result = fwrite($fp, substr($data, $written))) === FALSE)
            {
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return is_int($result);
    }

    /**
     * Delete Files
     *
     * Deletes all files contained in the supplied directory path.
     * Files must be writable or owned by the system in order to be deleted.
     * If the second parameter is set to TRUE, any directories contained
     * within the supplied base directory will be nuked as well.
     *
     * @param	string	$path		File path
     * @param	bool	$del_dir	Whether to delete any directories found in the path
     * @param	bool	$htdocs		Whether to skip deleting .htaccess and index page files
     * @param	int	$_level		Current directory depth level (default: 0; internal use only)
     * @return	bool
     */
    public static function delete_files($path, $del_dir = FALSE, $htdocs = FALSE, $_level = 0)
    {
        // Trim the trailing slash
        $path = rtrim($path, '/\\');

        if ( ! $current_dir = @opendir($path))
        {
            return FALSE;
        }

        while (FALSE !== ($filename = @readdir($current_dir)))
        {
            if ($filename !== '.' && $filename !== '..')
            {
                $filepath = $path.DIRECTORY_SEPARATOR.$filename;

                if (is_dir($filepath) && $filename[0] !== '.' && ! is_link($filepath))
                {
                    delete_files($filepath, $del_dir, $htdocs, $_level + 1);
                }
                elseif ($htdocs !== TRUE OR ! preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename))
                {
                    @unlink($filepath);
                }
            }
        }

        closedir($current_dir);

        return ($del_dir === TRUE && $_level > 0)
            ? @rmdir($path)
            : TRUE;
    }

    /**
     * Get Filenames
     *
     * Reads the specified directory and builds an array containing the filenames.
     * Any sub-folders contained within the specified path are read as well.
     *
     * @param	string	path to source
     * @param	bool	whether to include the path as part of the filename
     * @param	bool	internal variable to determine recursion status - do not use in calls
     * @return	array
     */
    public static function get_filenames($source_dir, $include_path = FALSE, $_recursion = FALSE)
    {
        static $_filedata = array();

        if ($fp = @opendir($source_dir))
        {
            // reset the array and make sure $source_dir has a trailing slash on the initial call
            if ($_recursion === FALSE)
            {
                $_filedata = array();
                $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            }

            while (FALSE !== ($file = readdir($fp)))
            {
                if (is_dir($source_dir.$file) && $file[0] !== '.')
                {
                    self::get_filenames($source_dir.$file.DIRECTORY_SEPARATOR, $include_path, TRUE);
                }
                elseif ($file[0] !== '.')
                {
                    $_filedata[] = ($include_path === TRUE) ? $source_dir.$file : $file;
                }
            }

            closedir($fp);
            return $_filedata;
        }

        return $_filedata;
    }

    /**
     * Get Directory File Information
     *
     * Reads the specified directory and builds an array containing the filenames,
     * filesize, dates, and permissions
     *
     * Any sub-folders contained within the specified path are read as well.
     *
     * @param	string	path to source
     * @param	bool	Look only at the top level directory specified?
     * @param	bool	internal variable to determine recursion status - do not use in calls
     * @return	array
     */
    public static function get_dir_file_info($source_dir, $top_level_only = TRUE, $_recursion = FALSE)
    {
        static $_filedata = array();
        $relative_path = $source_dir;
        if ($fp = @opendir($source_dir))
        {
            // reset the array and make sure $source_dir has a trailing slash on the initial call
            if ($_recursion === FALSE)
            {
                $_filedata = array();
                $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            }

            // Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
            while (FALSE !== ($file = readdir($fp)))
            {
                if (is_dir($source_dir.$file) && $file[0] !== '.' && $top_level_only === FALSE)
                {
                    get_dir_file_info($source_dir.$file.DIRECTORY_SEPARATOR, $top_level_only, TRUE);
                }
                elseif ($file[0] !== '.')
                {
                    $_filedata[$file] = self::get_file_info($source_dir.$file);
                    $_filedata[$file]['relative_path'] = $relative_path;
                }
            }

            closedir($fp);
            return $_filedata;
        }

        return $_filedata;
    }

    /**
     * Get File Info
     *
     * Given a file and path, returns the name, path, size, date modified
     * Second parameter allows you to explicitly declare what information you want returned
     * Options are: name, server_path, size, date, readable, writable, executable, fileperms
     * Returns FALSE if the file cannot be found.
     *
     * @param	string	path to file
     * @param	mixed	array or comma separated string of information returned
     * @return	array
     */
    public static function get_file_info($file, $returned_values = array('name', 'server_path', 'size', 'date'))
    {
        if ( ! file_exists($file))
        {
            return FALSE;
        }

        if (is_string($returned_values))
        {
            $returned_values = explode(',', $returned_values);
        }

        foreach ($returned_values as $key)
        {
            switch ($key)
            {
                case 'name':
                    $fileinfo['name'] = basename($file);
                    break;
                case 'server_path':
                    $fileinfo['server_path'] = $file;
                    break;
                case 'size':
                    $fileinfo['size'] = filesize($file);
                    break;
                case 'date':
                    $fileinfo['date'] = filemtime($file);
                    break;
                case 'readable':
                    $fileinfo['readable'] = is_readable($file);
                    break;
                case 'writable':
                    $fileinfo['writable'] = self::is_really_writable($file);
                    break;
                case 'executable':
                    $fileinfo['executable'] = is_executable($file);
                    break;
                case 'fileperms':
                    $fileinfo['fileperms'] = fileperms($file);
                    break;
            }
        }

        return $fileinfo;
    }


    /**
     * Get Mime by Extension
     *
     * Translates a file extension into a mime type based on config/mimes.php.
     * Returns FALSE if it can't determine the type, or open the mime config file
     *
     * Note: this is NOT an accurate way of determining file mime types, and is here strictly as a convenience
     * It should NOT be trusted, and should certainly NOT be used for security
     *
     * @param	string	$filename	File name
     * @return	string
     */
    public static function get_mime_by_extension($filename)
    {
        static $mimes;

        if ( ! is_array($mimes))
        {
            $mimes = get_mimes();

            if (empty($mimes))
            {
                return FALSE;
            }
        }

        $extension = strtolower(substr(strrchr($filename, '.'), 1));

        if (isset($mimes[$extension]))
        {
            return is_array($mimes[$extension])
                ? current($mimes[$extension]) // Multiple mime types, just give the first one
                : $mimes[$extension];
        }

        return FALSE;
    }


    /**
     * Symbolic Permissions
     *
     * Takes a numeric value representing a file's permissions and returns
     * standard symbolic notation representing that value
     *
     * @param	int	$perms	Permissions
     * @return	string
     */
    public static function symbolic_permissions($perms)
    {
        if (($perms & 0xC000) === 0xC000)
        {
            $symbolic = 's'; // Socket
        }
        elseif (($perms & 0xA000) === 0xA000)
        {
            $symbolic = 'l'; // Symbolic Link
        }
        elseif (($perms & 0x8000) === 0x8000)
        {
            $symbolic = '-'; // Regular
        }
        elseif (($perms & 0x6000) === 0x6000)
        {
            $symbolic = 'b'; // Block special
        }
        elseif (($perms & 0x4000) === 0x4000)
        {
            $symbolic = 'd'; // Directory
        }
        elseif (($perms & 0x2000) === 0x2000)
        {
            $symbolic = 'c'; // Character special
        }
        elseif (($perms & 0x1000) === 0x1000)
        {
            $symbolic = 'p'; // FIFO pipe
        }
        else
        {
            $symbolic = 'u'; // Unknown
        }

        // Owner
        $symbolic .= (($perms & 0x0100) ? 'r' : '-')
            .(($perms & 0x0080) ? 'w' : '-')
            .(($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $symbolic .= (($perms & 0x0020) ? 'r' : '-')
            .(($perms & 0x0010) ? 'w' : '-')
            .(($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

        // World
        $symbolic .= (($perms & 0x0004) ? 'r' : '-')
            .(($perms & 0x0002) ? 'w' : '-')
            .(($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

        return $symbolic;
    }

    /**
     * Octal Permissions
     *
     * Takes a numeric value representing a file's permissions and returns
     * a three character string representing the file's octal permissions
     *
     * @param	int	$perms	Permissions
     * @return	string
     */
    public static function octal_permissions($perms)
    {
        return substr(sprintf('%o', $perms), -3);
    }

    /**
     * Tests for file writability
     *
     * is_writable() returns TRUE on Windows servers when you really can't write to
     * the file, based on the read-only attribute. is_writable() is also unreliable
     * on Unix servers if safe_mode is on.
     *
     * @link	https://bugs.php.net/bug.php?id=54709
     * @param	string
     * @return	bool
     */
    public static function is_really_writable($file)
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR === '/' && (is_php('5.4') OR ! ini_get('safe_mode')))
        {
            return is_writable($file);
        }

        /* For Windows servers and safe_mode "on" installations we'll actually
         * write a file then read it. Bah...
         */
        if (is_dir($file))
        {
            $file = rtrim($file, '/').'/'.md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE)
            {
                return FALSE;
            }

            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;
        }
        elseif ( ! is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE)
        {
            return FALSE;
        }

        fclose($fp);
        return TRUE;
    }

    /**
     * Create a Directory Map
     *
     * Reads the specified directory and builds an array
     * representation of it. Sub-folders contained with the
     * directory will be mapped as well.
     *
     * @param	string	$source_dir		Path to source
     * @param	int	$directory_depth	Depth of directories to traverse
     *						(0 = fully recursive, 1 = current dir, etc)
     * @param	bool	$hidden			Whether to show hidden files
     * @return	array
     */
    public static function directory_map($source_dir, $directory_depth = 0, $hidden = FALSE)
    {
        if ($fp = @opendir($source_dir))
        {
            $filedata	= array();
            $new_depth	= $directory_depth - 1;
            $source_dir	= rtrim($source_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

            while (FALSE !== ($file = readdir($fp)))
            {
                // Remove '.', '..', and hidden files [optional]
                if ($file === '.' OR $file === '..' OR ($hidden === FALSE && $file[0] === '.'))
                {
                    continue;
                }

                is_dir($source_dir.$file) && $file .= DIRECTORY_SEPARATOR;

                if (($directory_depth < 1 OR $new_depth > 0) && is_dir($source_dir.$file))
                {
                    $filedata[$file] = self::directory_map($source_dir.$file, $new_depth, $hidden);
                }
                else
                {
                    $filedata[] = $file;
                }
            }

            closedir($fp);
            return $filedata;
        }

        return FALSE;
    }
}