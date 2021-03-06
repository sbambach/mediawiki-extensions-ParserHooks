<?php

/**
 * Initialization file for the ParserHooks MediaWiki extension.
 *
 * Documentation: https://github.com/wikimedia/mediawiki-extensions-ParserHooks/blob/master/README.md
 * Support: https://www.mediawiki.org/wiki/Extension_talk:ParserHooks
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( defined( 'ParserHooks_VERSION' ) ) {
	// Do not initialize more then once.
	return;
}

define( 'ParserHooks_VERSION', '1.2' );

// Attempt to include the dependencies if one has not been loaded yet.
// This is the path to the autoloader generated by composer in case of a composer install.
if ( !defined( 'ParamProcessor_VERSION' ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once( __DIR__ . '/vendor/autoload.php' );
}

// Attempt to include the ParamProcessor lib if that hasn't been done yet.
// This is the path the ParamProcessor entry point will be at when loaded as MediaWiki extension.
if ( !defined( 'ParamProcessor_VERSION' ) && is_readable( __DIR__ . '/../Validator/Validator.php' ) ) {
	include_once( __DIR__ . '/../Validator/Validator.php' );
}

// Only initialize the extension when all dependencies are present.
if ( !defined( 'ParamProcessor_VERSION' ) ) {
	throw new Exception( 'You need to have the ParamProcessor library loaded in order to use ParserHooks' );
}

// @codeCoverageIgnoreStart
spl_autoload_register( function ( $className ) {
	$className = ltrim( $className, '\\' );
	$fileName = '';
	$namespace = '';

	if ( $lastNsPos = strripos( $className, '\\') ) {
		$namespace = substr( $className, 0, $lastNsPos );
		$className = substr( $className, $lastNsPos + 1 );
		$fileName  = str_replace( '\\', '/', $namespace ) . '/';
	}

	$fileName .= str_replace( '_', '/', $className ) . '.php';

	$namespaceSegments = explode( '\\', $namespace );

	if ( $namespaceSegments[0] === 'ParserHooks' ) {
		if ( count( $namespaceSegments ) === 1 || $namespaceSegments[1] !== 'Tests' ) {
			require_once __DIR__ . '/src/' . $fileName;
		}
	}
} );

call_user_func( function() {

	global $wgExtensionCredits, $wgExtensionMessagesFiles, $wgHooks;

	$wgExtensionCredits['other'][] = array(
		'path' => __FILE__,
		'name' => 'ParserHooks',
		'version' => ParserHooks_VERSION,
		'author' => array(
			'[https://www.mediawiki.org/wiki/User:Jeroen_De_Dauw Jeroen De Dauw]',
		),
		'url' => 'https://www.mediawiki.org/wiki/Extension:ParserHooks',
		'descriptionmsg' => 'parserhooks-desc'
	);

	$wgExtensionMessagesFiles['ParserHooksExtension'] = __DIR__ . '/ParserHooks.i18n.php';

	/**
	 * Hook to add PHPUnit test cases.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/UnitTestsList
	 *
	 * @since 1.0
	 *
	 * @param array $files
	 *
	 * @return boolean
	 */
	$wgHooks['UnitTestsList'][]	= function( array &$files ) {
		$directoryIterator = new RecursiveDirectoryIterator( __DIR__ . '/tests/' );

		/**
		 * @var SplFileInfo $fileInfo
		 */
		foreach ( new RecursiveIteratorIterator( $directoryIterator ) as $fileInfo ) {
			if ( substr( $fileInfo->getFilename(), -8 ) === 'Test.php' ) {
				$files[] = $fileInfo->getPathname();
			}
		}

		return true;
	};

} );
// @codeCoverageIgnoreEnd
