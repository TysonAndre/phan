<?php
declare(strict_types=1);

/**
 * This lists all of the possible real return types of various global functions.
 * This is useful because php won't provide many of these until php 8,
 * and even then won't be able to represent types such as string|false.
 *
 * This is conservative to avoid false positives, and includes types returned for all possible failure modes
 * (invalid arguments/argument counts, spurious errors, etc.)
 *
 * Generated by Phan's internal/extract_arg_info.php, from ext/opcache/Optimizer/zend_func_info.c of php-src.
 * (for the php 8.0-dev branch)
 *
 * Note that this has less entries than the php 7 map because Reflection already has accurate real types.
 * For accurate real condition detection of code targeting php 8.0-dev, run Phan with php 8.0-dev.
 */
return [
'_' => 'false|string',
'addcslashes' => 'string',
'array_change_key_case' => 'array',
'array_chunk' => 'array',
'array_column' => 'array',
'array_combine' => 'array',
'array_count_values' => 'int[]',
'array_diff' => 'array',
'array_diff_assoc' => 'array',
'array_diff_key' => 'array',
'array_diff_uassoc' => 'array',
'array_diff_ukey' => 'array',
'array_fill' => 'array<int,mixed>',
'array_fill_keys' => 'array',
'array_filter' => 'array',
'array_flip' => 'int[]|string[]',
'array_intersect' => 'array',
'array_intersect_assoc' => 'array',
'array_intersect_key' => 'array',
'array_intersect_uassoc' => 'array',
'array_intersect_ukey' => 'array',
'array_keys' => 'list<int>|list<string>',
'array_map' => 'array',
'array_merge' => 'array',
'array_merge_recursive' => 'array',
'array_pad' => 'array',
'array_replace' => 'array',
'array_replace_recursive' => 'array',
'array_reverse' => 'array',
'array_slice' => 'array',
'array_splice' => 'array',
'array_udiff' => 'array',
'array_udiff_assoc' => 'array',
'array_udiff_uassoc' => 'array',
'array_uintersect' => 'array',
'array_uintersect_assoc' => 'array',
'array_uintersect_uassoc' => 'array',
'array_unique' => 'array',
'array_values' => 'list<mixed>',
'array_walk' => 'true',
'array_walk_recursive' => 'true',
'arsort' => 'true',
'asort' => 'true',
'assert_options' => '?array<int,object>|?array<int,string>|?false|?int|?object|?string',
'base64_decode' => 'false|string',
'base64_encode' => 'string',
'base_convert' => 'string',
'basename' => 'string',
'bcadd' => 'string',
'bcdiv' => 'string',
'bcmod' => 'string',
'bcmul' => 'string',
'bcpow' => 'string',
'bcpowmod' => 'false|string',
'bcsqrt' => 'string',
'bcsub' => 'string',
'bin2hex' => 'string',
'bind_textdomain_codeset' => 'false|string',
'bindtextdomain' => 'false|string',
'chr' => 'string',
'chunk_split' => 'string',
'class_implements' => 'array<string,string>|false',
'class_parents' => 'array<string,string>|false',
'class_uses' => 'array<string,string>|false',
'closelog' => 'true',
'compact' => 'array<string,mixed>',
'constant' => '?array|?bool|?float|?int|?resource|?string',
'convert_uudecode' => 'false|string',
'convert_uuencode' => 'false|string',
'count_chars' => 'array<int,int>|string',
'crypt' => 'string',
'curl_copy_handle' => 'false|resource',
'curl_error' => 'string',
'curl_escape' => 'false|string',
'curl_file_create' => 'object',
'curl_getinfo' => '?array|?false|?float|?int|?string',
'curl_init' => 'false|resource',
'curl_multi_info_read' => 'array<string,int>|array<string,resource>|false',
'curl_multi_init' => 'resource',
'curl_multi_strerror' => '?string',
'curl_share_init' => 'resource',
'curl_strerror' => '?string',
'curl_unescape' => 'false|string',
'curl_version' => 'array<string,array>|array<string,int>|array<string,string>|false',
'date' => 'string',
'date_create' => 'false|object',
'date_create_from_format' => 'false|object',
'date_create_immutable' => 'false|object',
'date_create_immutable_from_format' => 'false|object',
'date_default_timezone_get' => 'string',
'date_diff' => 'object',
'date_format' => 'string',
'date_get_last_errors' => 'array<string,array>|array<string,int>|false',
'date_interval_create_from_date_string' => 'false|object',
'date_interval_format' => 'string',
'date_parse' => 'array<string,mixed>',
'date_parse_from_format' => 'array<string,mixed>',
'date_sun_info' => 'array<string,bool>|array<string,int>',
'date_sunrise' => 'false|float|int|string',
'date_sunset' => 'false|float|int|string',
'date_timezone_get' => 'false|object',
'dcgettext' => 'false|string',
'dcngettext' => 'false|string',
'debug_backtrace' => 'list<array>',
'decbin' => 'string',
'dechex' => 'string',
'decoct' => 'string',
'dgettext' => 'false|string',
'dir' => 'false|object',
'dirname' => 'string',
'dns_get_record' => 'list<array>|false',
'error_get_last' => '?array{type:int,message:string,file:string,line:int}',
'escapeshellarg' => 'string',
'escapeshellcmd' => 'string',
'exec' => 'false|string',
'exif_read_data' => 'array<string,mixed>|false',
'exif_tagname' => 'false|string',
'exif_thumbnail' => 'false|string',
'explode' => 'list<string>',
'fgetc' => 'false|string',
'fgetcsv' => '?list<?string>|?false',
'fgets' => 'false|string',
'file' => 'list<string>|false',
'file_get_contents' => 'false|string',
'filetype' => 'false|string',
'filter_input_array' => '?array|?false',
'filter_list' => 'list<string>',
'filter_var_array' => '?array|?false',
'finfo_buffer' => 'false|string',
'finfo_file' => 'false|string',
'finfo_open' => 'false|resource',
'fopen' => 'false|resource',
'fread' => 'false|string',
'fscanf' => '?list<mixed>|?false|?int',
'fsockopen' => 'false|resource',
'fstat' => 'false|int[]',
'func_get_args' => 'list<mixed>|false',
'gd_info' => 'array<string,bool>|array<string,string>',
'get_browser' => 'array<string,mixed>|false|object',
'get_cfg_var' => 'array[]|false|string|string[]',
'get_class_methods' => '?list<string>|?false',
'get_class_vars' => 'array<string,mixed>|false',
'get_current_user' => 'string',
'get_declared_classes' => 'list<string>',
'get_declared_interfaces' => 'list<string>',
'get_declared_traits' => 'list<string>',
'get_defined_constants' => 'array<string,?array>|array<string,?bool>|array<string,?float>|array<string,?int>|array<string,?resource>|array<string,?string>',
'get_defined_functions' => 'array<string,array>',
'get_defined_vars' => 'array<string,mixed>',
'get_extension_funcs' => 'list<string>|false',
'get_headers' => 'array[]|false|string[]',
'get_html_translation_table' => 'array<string,string>',
'get_include_path' => 'false|string',
'get_included_files' => 'list<string>',
'get_loaded_extensions' => 'list<string>',
'get_magic_quotes_gpc' => 'false',
'get_magic_quotes_runtime' => 'false',
'get_mangled_object_vars' => 'array',
'get_meta_tags' => 'array<string,string>|false',
'get_object_vars' => 'array',
'get_resource_type' => 'string',
'getcwd' => 'false|string',
'getdate' => 'array<string,int>|array<string,string>',
'getenv' => 'array<string,string>|false|string',
'gethostbyaddr' => 'false|string',
'gethostbyname' => 'string',
'gethostbynamel' => 'list<string>|false',
'gethostname' => 'false|string',
'getimagesize' => 'false|int[]|string[]',
'getimagesizefromstring' => 'false|int[]|string[]',
'getopt' => 'array[]|bool[]|false|string[]',
'getprotobynumber' => 'false|string',
'getrusage' => 'array<string,int>|false',
'getservbyport' => 'false|string',
'gettext' => 'false|string',
'gettimeofday' => 'array<string,int>|float',
'glob' => 'list<string>|false',
'gmdate' => 'string',
'gmstrftime' => 'false|string',
'gzcompress' => 'false|string',
'gzdecode' => 'false|string',
'gzdeflate' => 'false|string',
'gzencode' => 'false|string',
'gzfile' => 'list<string>|false',
'gzgetc' => 'false|string',
'gzgets' => 'false|string',
'gzinflate' => 'false|string',
'gzopen' => '?false|?resource',
'gzread' => 'false|string',
'gzuncompress' => 'false|string',
'hash' => 'false|string',
'hash_algos' => 'list<string>',
'hash_copy' => '?false|?object',
'hash_file' => '?false|?string',
'hash_final' => '?string',
'hash_hkdf' => '?false|?string',
'hash_hmac' => '?false|?string',
'hash_hmac_algos' => 'list<string>',
'hash_hmac_file' => '?false|?string',
'hash_init' => '?false|?object',
'hash_pbkdf2' => '?false|?string',
'headers_list' => 'list<string>',
'hebrev' => 'string',
'hex2bin' => 'false|string',
'highlight_file' => '?bool|?string',
'highlight_string' => '?bool|?string',
'htmlentities' => 'string',
'htmlspecialchars' => 'string',
'http_build_query' => 'false|string',
'iconv' => 'false|string',
'iconv_get_encoding' => 'array<string,string>|false|string',
'iconv_mime_decode' => 'false|string',
'iconv_mime_decode_headers' => 'array<string,array>|array<string,string>|false',
'iconv_mime_encode' => 'false|string',
'iconv_substr' => 'false|string',
'image_type_to_extension' => 'false|string',
'image_type_to_mime_type' => 'string',
'imageaffine' => 'false|object',
'imageaffinematrixconcat' => 'array<int,float>|false',
'imageaffinematrixget' => 'array<int,float>|false',
'imagecolorset' => '?false',
'imagecolorsforindex' => 'array<string,int>|false',
'imagecreate' => 'false|object',
'imagecreatefrombmp' => 'false|object',
'imagecreatefromgd' => 'false|object',
'imagecreatefromgd2' => 'false|object',
'imagecreatefromgd2part' => 'false|object',
'imagecreatefromgif' => 'false|object',
'imagecreatefromjpeg' => 'false|object',
'imagecreatefrompng' => 'false|object',
'imagecreatefromstring' => 'false|object',
'imagecreatefromwbmp' => 'false|object',
'imagecreatefromwebp' => 'false|object',
'imagecreatefromxbm' => 'false|object',
'imagecreatefromxpm' => 'false|object',
'imagecreatetruecolor' => 'false|object',
'imagecrop' => 'false|object',
'imagecropauto' => 'false|object',
'imageftbbox' => 'array<int,int>|false',
'imagefttext' => 'array<int,int>|false',
'imagegetclip' => 'array<int,int>',
'imagegrabscreen' => 'false|object',
'imagegrabwindow' => 'false|object',
'imageresolution' => 'array<int,int>|true',
'imagerotate' => 'false|object',
'imagescale' => 'false|object',
'imagettfbbox' => 'array<int,int>|false',
'imagettftext' => 'array<int,int>|false',
'inet_ntop' => 'false|string',
'inet_pton' => 'false|string',
'ini_alter' => 'false|string',
'ini_get_all' => 'array<string,?array>|array<string,?string>|false',
'iptcembed' => 'false|string',
'iptcparse' => 'array<string,array>|false',
'iterator_to_array' => 'array',
'json_encode' => 'false|string',
'json_last_error_msg' => 'string',
'krsort' => 'true',
'ksort' => 'true',
'localeconv' => 'array<string,array>|array<string,int>|array<string,string>',
'localtime' => 'int[]',
'long2ip' => 'false|string',
'lstat' => 'bool[]|false|int[]|string[]',
'mb_convert_case' => 'false|string',
'mb_convert_encoding' => 'array|false|string',
'mb_convert_kana' => 'false|string',
'mb_convert_variables' => 'false|string',
'mb_decode_mimeheader' => 'false|string',
'mb_decode_numericentity' => 'false|string',
'mb_detect_encoding' => 'false|string',
'mb_detect_order' => '?list<string>|?false',
'mb_encode_mimeheader' => 'false|string',
'mb_encode_numericentity' => 'false|string',
'mb_encoding_aliases' => 'list<string>|false',
'mb_ereg_replace' => '?false|?string',
'mb_ereg_replace_callback' => '?false|?string',
'mb_ereg_search_getregs' => 'array<int,bool>|array<int,string>|false',
'mb_ereg_search_pos' => 'array<int,int>|false',
'mb_ereg_search_regs' => 'array<int,bool>|array<int,string>|false',
'mb_eregi_replace' => '?false|?string',
'mb_get_info' => 'array[]|false|int|int[]|string|string[]',
'mb_http_input' => 'array<int,string>|false|string',
'mb_http_output' => 'bool|string',
'mb_internal_encoding' => 'bool|string',
'mb_language' => 'bool|string',
'mb_list_encodings' => 'array<int,string>',
'mb_output_handler' => 'string',
'mb_preferred_mime_name' => 'false|string',
'mb_regex_encoding' => 'bool|string',
'mb_regex_set_options' => 'string',
'mb_split' => 'list<string>|false',
'mb_strcut' => 'false|string',
'mb_strimwidth' => 'false|string',
'mb_stristr' => 'false|string',
'mb_strrchr' => 'false|string',
'mb_strrichr' => 'false|string',
'mb_strstr' => 'false|string',
'mb_strtolower' => 'false|string',
'mb_strtoupper' => 'false|string',
'mb_substitute_character' => 'bool|int|string',
'mb_substr' => 'false|string',
'md5' => 'string',
'md5_file' => 'false|string',
'metaphone' => 'false|string',
'mhash' => 'false|string',
'mhash_get_hash_name' => '?false|?string',
'mhash_keygen_s2k' => '?false|?string',
'microtime' => 'float|string',
'mime_content_type' => 'false|string',
'mysqli_affected_rows' => '?int|?string',
'mysqli_autocommit' => '?bool',
'mysqli_begin_transaction' => '?bool',
'mysqli_change_user' => '?bool',
'mysqli_character_set_name' => '?string',
'mysqli_close' => '?true',
'mysqli_commit' => '?bool',
'mysqli_connect' => '?false|?object',
'mysqli_connect_errno' => 'int',
'mysqli_connect_error' => '?string',
'mysqli_data_seek' => '?bool',
'mysqli_debug' => '?true',
'mysqli_dump_debug_info' => '?bool',
'mysqli_errno' => '?int',
'mysqli_error' => '?string',
'mysqli_error_list' => '?array<int,array>',
'mysqli_fetch_all' => '?array|?false',
'mysqli_fetch_array' => '?array|?false',
'mysqli_fetch_assoc' => '?array<string,mixed>|?false',
'mysqli_fetch_field' => '?false|?object',
'mysqli_fetch_field_direct' => '?false|?object',
'mysqli_fetch_fields' => '?array<int,object>',
'mysqli_fetch_lengths' => '?array<int,int>|?false',
'mysqli_fetch_object' => '?false|?object',
'mysqli_fetch_row' => '?array<int,mixed>|?false',
'mysqli_field_count' => '?int',
'mysqli_field_seek' => '?bool',
'mysqli_field_tell' => '?int',
'mysqli_free_result' => 'void',
'mysqli_get_client_info' => 'string',
'mysqli_get_client_version' => 'int',
'mysqli_get_host_info' => '?string',
'mysqli_get_links_stats' => '?array<string,int>',
'mysqli_get_proto_info' => '?int',
'mysqli_get_server_info' => '?string',
'mysqli_get_server_version' => '?int',
'mysqli_get_warnings' => '?false|?object',
'mysqli_info' => '?string',
'mysqli_init' => 'false|object',
'mysqli_kill' => '?bool',
'mysqli_more_results' => '?bool',
'mysqli_multi_query' => '?bool',
'mysqli_next_result' => '?bool',
'mysqli_num_fields' => '?false|?int',
'mysqli_num_rows' => '?false|?int|?string',
'mysqli_options' => '?bool',
'mysqli_ping' => '?bool',
'mysqli_poll' => '?false|?int',
'mysqli_prepare' => '?false|?object',
'mysqli_query' => '?bool|?object',
'mysqli_real_connect' => '?bool',
'mysqli_real_escape_string' => '?string',
'mysqli_real_query' => '?bool',
'mysqli_reap_async_query' => '?bool|?object',
'mysqli_refresh' => '?bool',
'mysqli_release_savepoint' => '?bool',
'mysqli_rollback' => '?bool',
'mysqli_savepoint' => '?bool',
'mysqli_select_db' => '?bool',
'mysqli_set_charset' => '?bool',
'mysqli_sqlstate' => '?string',
'mysqli_ssl_set' => '?true',
'mysqli_stmt_attr_get' => '?false|?int',
'mysqli_stmt_attr_set' => '?bool',
'mysqli_stmt_bind_param' => '?bool',
'mysqli_stmt_bind_result' => '?bool',
'mysqli_stmt_close' => '?bool',
'mysqli_stmt_data_seek' => '?false',
'mysqli_stmt_errno' => '?int',
'mysqli_stmt_error' => '?string',
'mysqli_stmt_error_list' => '?array<int,array>',
'mysqli_stmt_execute' => '?bool',
'mysqli_stmt_fetch' => '?bool',
'mysqli_stmt_field_count' => '?int',
'mysqli_stmt_free_result' => 'void',
'mysqli_stmt_get_result' => '?false|?object',
'mysqli_stmt_get_warnings' => '?false|?object',
'mysqli_stmt_init' => '?false|?object',
'mysqli_stmt_insert_id' => '?int|?string',
'mysqli_stmt_more_results' => '?bool',
'mysqli_stmt_next_result' => '?bool',
'mysqli_stmt_num_rows' => '?int|?string',
'mysqli_stmt_param_count' => '?int',
'mysqli_stmt_prepare' => '?bool',
'mysqli_stmt_reset' => '?bool',
'mysqli_stmt_result_metadata' => '?false|?object',
'mysqli_stmt_send_long_data' => '?bool',
'mysqli_stmt_sqlstate' => '?string',
'mysqli_stmt_store_result' => '?bool',
'mysqli_store_result' => '?false|?object',
'mysqli_thread_id' => '?int',
'mysqli_thread_safe' => 'bool',
'mysqli_use_result' => '?false|?object',
'mysqli_warning_count' => '?int',
'ngettext' => 'false|string',
'nl_langinfo' => 'false|string',
'number_format' => 'string',
'ob_get_clean' => 'false|string',
'ob_get_flush' => 'false|string',
'ob_get_status' => 'array[]|int[]|string[]',
'ob_gzhandler' => 'false|string',
'ob_list_handlers' => 'list<string>',
'opendir' => 'false|resource',
'pack' => 'false|string',
'parse_ini_file' => '(?array)[]|(?bool)[]|(?float)[]|(?int)[]|(?string)[]|false',
'parse_ini_string' => '(?array)[]|(?bool)[]|(?float)[]|(?int)[]|(?string)[]|false',
'parse_url' => 'array|false|int|null|string',
'passthru' => '?false',
'password_get_info' => '?array<string,array>|?array<string,int>|?array<string,string>',
'password_hash' => 'string',
'pathinfo' => 'array|string',
'pfsockopen' => 'false|resource',
'pg_affected_rows' => '?false|?int',
'pg_cancel_query' => 'bool',
'pg_close' => '?bool',
'pg_connect' => '?false|?resource',
'pg_connect_poll' => '?false|?int',
'pg_connection_busy' => 'bool',
'pg_connection_reset' => 'bool',
'pg_connection_status' => 'false|int',
'pg_consume_input' => '?bool',
'pg_convert' => '?array<string,mixed>|?false',
'pg_copy_from' => '?bool',
'pg_copy_to' => '?array<int,string>|?false',
'pg_dbname' => '?false|?string',
'pg_delete' => '?bool|?string',
'pg_end_copy' => '?bool',
'pg_escape_bytea' => '?false|?string',
'pg_escape_identifier' => '?false|?string',
'pg_escape_literal' => '?false|?string',
'pg_escape_string' => '?false|?string',
'pg_execute' => '?false|?resource',
'pg_fetch_all' => '?array<int,array>|?false',
'pg_fetch_all_columns' => '?array<int,?string>|?false',
'pg_fetch_array' => '?(?string)[]|?false',
'pg_fetch_assoc' => '?array<string,?string>|?false',
'pg_fetch_object' => '?false|?object',
'pg_fetch_result' => '?false|?string',
'pg_fetch_row' => '?array<int,?string>|?false',
'pg_field_is_null' => '?false|?int',
'pg_field_name' => '?false|?string',
'pg_field_num' => '?false|?int',
'pg_field_prtlen' => '?false|?int',
'pg_field_size' => '?false|?int',
'pg_field_table' => '?false|?int|?string',
'pg_field_type' => '?false|?string',
'pg_field_type_oid' => '?false|?int|?string',
'pg_flush' => '?bool|?int',
'pg_free_result' => '?bool',
'pg_get_notify' => 'array|false',
'pg_get_pid' => 'false|int',
'pg_get_result' => 'false|resource',
'pg_host' => '?false|?string',
'pg_insert' => '?bool|?resource|?string',
'pg_last_error' => '?false|?string',
'pg_last_oid' => '?false|?int|?string',
'pg_lo_create' => '?false|?int|?string',
'pg_lo_export' => '?bool',
'pg_lo_import' => '?false|?int|?string',
'pg_lo_open' => '?false|?resource',
'pg_lo_read' => '?false|?string',
'pg_lo_read_all' => '?false|?int',
'pg_lo_seek' => '?bool',
'pg_lo_tell' => '?false|?int',
'pg_lo_truncate' => '?bool',
'pg_lo_unlink' => '?bool',
'pg_lo_write' => '?false|?int',
'pg_meta_data' => '?array<string,array>|?false',
'pg_num_fields' => '?false|?int',
'pg_num_rows' => '?false|?int',
'pg_options' => '?false|?string',
'pg_parameter_status' => 'false|string',
'pg_pconnect' => '?false|?resource',
'pg_ping' => 'bool',
'pg_port' => '?false|?string',
'pg_prepare' => '?false|?resource',
'pg_put_line' => '?bool',
'pg_query' => '?false|?resource',
'pg_query_params' => '?false|?resource',
'pg_result_error' => 'false|string',
'pg_result_error_field' => 'false|null|string',
'pg_result_seek' => '?bool',
'pg_result_status' => '?false|?int|?string',
'pg_select' => '?bool|?string',
'pg_send_execute' => '?bool|?int',
'pg_send_prepare' => '?bool|?int',
'pg_send_query' => '?bool|?int',
'pg_send_query_params' => '?bool|?int',
'pg_set_client_encoding' => '?false|?int',
'pg_set_error_verbosity' => '?false|?int',
'pg_socket' => '?false|?resource',
'pg_trace' => '?bool',
'pg_transaction_status' => 'false|int',
'pg_tty' => '?false|?string',
'pg_unescape_bytea' => '?false|?string',
'pg_untrace' => '?bool',
'pg_update' => '?bool|?string',
'pg_version' => '?array<string,string>|?false',
'php_ini_loaded_file' => 'false|string',
'php_ini_scanned_files' => 'false|string',
'php_sapi_name' => 'false|string',
'php_strip_whitespace' => 'string',
'php_uname' => 'string',
'phpcredits' => 'true',
'phpinfo' => 'true',
'phpversion' => 'false|string',
'popen' => 'false|resource',
'pow' => '?float|?int|?object',
'preg_filter' => '?string|?string[]',
'preg_grep' => 'array|false',
'preg_replace' => '?string|?string[]',
'preg_replace_callback' => '?string|?string[]',
'preg_split' => '?list<array>|?list<string>|?false',
'print_r' => 'string|true',
'proc_get_status' => 'array<string,bool>|array<string,int>|array<string,string>',
'proc_open' => 'false|resource',
'quoted_printable_decode' => 'string',
'quoted_printable_encode' => 'string',
'quotemeta' => 'string',
'random_bytes' => 'string',
'rawurldecode' => 'string',
'rawurlencode' => 'string',
'readdir' => 'false|string',
'readlink' => 'false|string',
'realpath' => 'false|string',
'realpath_cache_get' => 'array<string,array>',
'register_shutdown_function' => '?false',
'restore_error_handler' => 'true',
'restore_exception_handler' => 'true',
'scandir' => 'list<string>|false',
'serialize' => 'string',
'session_cache_limiter' => 'false|string',
'session_create_id' => 'false|string',
'session_encode' => 'false|string',
'session_get_cookie_params' => 'array<string,mixed>',
'session_module_name' => 'false|string',
'session_name' => 'false|string',
'session_save_path' => 'false|string',
'set_error_handler' => '?callable',
'set_include_path' => 'false|string',
'sha1' => 'string',
'sha1_file' => 'false|string',
'shell_exec' => '?false|?string',
'show_source' => '?false|?string',
'shuffle' => 'true',
'socket_get_status' => 'array<string,mixed>',
'sodium_bin2hex' => 'string',
'sodium_crypto_aead_aes256gcm_decrypt' => 'false|string',
'sodium_crypto_aead_aes256gcm_encrypt' => 'string',
'sodium_crypto_aead_aes256gcm_keygen' => 'string',
'sodium_crypto_aead_chacha20poly1305_decrypt' => 'false|string',
'sodium_crypto_aead_chacha20poly1305_encrypt' => 'string',
'sodium_crypto_aead_chacha20poly1305_ietf_decrypt' => 'false|string',
'sodium_crypto_aead_chacha20poly1305_ietf_encrypt' => 'string',
'sodium_crypto_aead_chacha20poly1305_ietf_keygen' => 'string',
'sodium_crypto_aead_chacha20poly1305_keygen' => 'string',
'sodium_crypto_aead_xchacha20poly1305_ietf_decrypt' => '?false|?string',
'sodium_crypto_aead_xchacha20poly1305_ietf_encrypt' => 'string',
'sodium_crypto_aead_xchacha20poly1305_ietf_keygen' => 'string',
'sodium_crypto_auth' => '?string',
'sodium_crypto_auth_keygen' => 'string',
'sodium_crypto_box' => 'string',
'sodium_crypto_box_keypair' => 'string',
'sodium_crypto_box_keypair_from_secretkey_and_publickey' => 'string',
'sodium_crypto_box_open' => 'false|string',
'sodium_crypto_box_publickey' => 'string',
'sodium_crypto_box_publickey_from_secretkey' => 'string',
'sodium_crypto_box_seal' => 'string',
'sodium_crypto_box_seal_open' => 'false|string',
'sodium_crypto_box_secretkey' => 'string',
'sodium_crypto_box_seed_keypair' => 'string',
'sodium_crypto_generichash' => 'string',
'sodium_crypto_generichash_final' => 'string',
'sodium_crypto_generichash_init' => 'string',
'sodium_crypto_generichash_keygen' => 'string',
'sodium_crypto_generichash_update' => 'true',
'sodium_crypto_kdf_derive_from_key' => 'string',
'sodium_crypto_kdf_keygen' => 'string',
'sodium_crypto_kx_client_session_keys' => '?array<int,string>',
'sodium_crypto_kx_keypair' => 'string',
'sodium_crypto_kx_publickey' => '?string',
'sodium_crypto_kx_secretkey' => '?string',
'sodium_crypto_kx_seed_keypair' => 'string',
'sodium_crypto_kx_server_session_keys' => '?array<int,string>',
'sodium_crypto_pwhash' => 'string',
'sodium_crypto_pwhash_scryptsalsa208sha256' => 'string',
'sodium_crypto_pwhash_scryptsalsa208sha256_str' => 'string',
'sodium_crypto_pwhash_str' => 'string',
'sodium_crypto_scalarmult' => 'string',
'sodium_crypto_secretbox' => 'string',
'sodium_crypto_secretbox_keygen' => 'string',
'sodium_crypto_secretbox_open' => 'false|string',
'sodium_crypto_shorthash' => 'string',
'sodium_crypto_shorthash_keygen' => 'string',
'sodium_crypto_sign' => 'string',
'sodium_crypto_sign_detached' => 'string',
'sodium_crypto_sign_ed25519_pk_to_curve25519' => 'string',
'sodium_crypto_sign_ed25519_sk_to_curve25519' => 'string',
'sodium_crypto_sign_keypair' => 'string',
'sodium_crypto_sign_keypair_from_secretkey_and_publickey' => 'string',
'sodium_crypto_sign_open' => 'false|string',
'sodium_crypto_sign_publickey' => 'string',
'sodium_crypto_sign_publickey_from_secretkey' => 'string',
'sodium_crypto_sign_secretkey' => 'string',
'sodium_crypto_sign_seed_keypair' => 'string',
'sodium_crypto_stream' => 'string',
'sodium_crypto_stream_keygen' => 'string',
'sodium_crypto_stream_xor' => 'string',
'sodium_hex2bin' => 'string',
'sodium_pad' => 'string',
'sodium_unpad' => 'string',
'sort' => 'true',
'soundex' => 'false|string',
'spl_classes' => 'array<string,string>',
'spl_object_hash' => 'string',
'sprintf' => 'string',
'sscanf' => '?list<mixed>|?int',
'stat' => 'bool[]|false|int[]|string[]',
'str_getcsv' => 'list<?string>',
'str_ireplace' => 'array[]|object[]|string|string[]',
'str_repeat' => 'string',
'str_replace' => 'array[]|object[]|string|string[]',
'str_rot13' => 'string',
'str_shuffle' => 'string',
'str_split' => 'list<string>',
'str_word_count' => 'array<int,string>|int',
'strchr' => 'false|string',
'stream_bucket_make_writeable' => '?object',
'stream_context_create' => 'resource',
'stream_context_get_default' => 'false|resource',
'stream_context_get_options' => 'array<string,mixed>|false',
'stream_context_get_params' => 'array<string,mixed>|false',
'stream_context_set_default' => 'false|resource',
'stream_context_set_params' => 'bool',
'stream_filter_append' => 'false|resource',
'stream_filter_prepend' => 'false|resource',
'stream_get_contents' => 'false|string',
'stream_get_filters' => 'list<string>',
'stream_get_line' => 'false|string',
'stream_get_meta_data' => 'array<string,mixed>|false',
'stream_get_transports' => 'list<string>|false',
'stream_get_wrappers' => 'list<string>|false',
'stream_resolve_include_path' => 'false|string',
'stream_socket_accept' => 'false|resource',
'stream_socket_client' => 'false|resource',
'stream_socket_get_name' => 'false|string',
'stream_socket_pair' => 'array{0:resource,1:resource}|false',
'stream_socket_recvfrom' => 'false|string',
'stream_socket_server' => 'false|resource',
'strftime' => 'false|string',
'strip_tags' => 'string',
'stripcslashes' => 'string',
'stripslashes' => 'string',
'stristr' => 'false|string',
'strpbrk' => 'false|string',
'strptime' => 'array<string,int>|array<string,string>|false',
'strrchr' => 'false|string',
'strrev' => 'string',
'strstr' => 'false|string',
'strtok' => 'false|string',
'substr_replace' => 'false|string|string[]',
'sys_get_temp_dir' => 'string',
'sys_getloadavg' => 'array{0:float,1:float,2:float}|false',
'system' => 'false|string',
'tempnam' => 'false|string',
'textdomain' => 'false|string',
'time_nanosleep' => 'array<string,int>|bool',
'timezone_abbreviations_list' => 'array<string,array>',
'timezone_identifiers_list' => 'list<string>|false',
'timezone_location_get' => 'array<string,float>|array<string,string>|false',
'timezone_name_from_abbr' => 'false|string',
'timezone_name_get' => 'string',
'timezone_open' => 'false|object',
'timezone_transitions_get' => 'array|false',
'timezone_version_get' => 'string',
'tmpfile' => 'false|resource',
'uasort' => 'true',
'ucwords' => 'string',
'uksort' => 'true',
'uniqid' => 'string',
'unpack' => 'array|false',
'urldecode' => 'string',
'urlencode' => 'string',
'usort' => 'true',
'utf8_decode' => 'string',
'utf8_encode' => 'string',
'var_export' => '?string',
'vsprintf' => 'string',
'wordwrap' => 'string',
'xml_error_string' => '?string',
'xml_parser_get_option' => '?false|?int|?string',
'zend_version' => 'string',
'zlib_decode' => 'false|string',
'zlib_encode' => 'false|string',
'zlib_get_coding_type' => 'false|string',
];
