<?php
/**
 * Methods to make links and related items.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

/**
 * Some internal bits split of from Skin.php. These functions are used
 * for primarily page content: links, embedded images, table of contents. Links
 * are also used in the skin.
 *
 * @ingroup Skins
 */
class Linker {

	/**
	 * Flags for userToolLinks()
	 */
	const TOOL_LINKS_NOBLOCK = 1;
	const TOOL_LINKS_EMAIL = 2;

	/**
	 * Get the appropriate HTML attributes to add to the "a" element of an
	 * external link, as created by [wikisyntax].
	 *
	 * @param string $class the contents of the class attribute; if an empty
	 *   string is passed, which is the default value, defaults to 'external'.
	 * @return string
	 * @deprecated since 1.18 Just pass the external class directly to something using Html::expandAttributes
	 */
	static function getExternalLinkAttributes( $class = 'external' ) {
		wfDeprecated( __METHOD__, '1.18' );
		return self::getLinkAttributesInternal( '', $class );
	}

	/**
	 * Get the appropriate HTML attributes to add to the "a" element of an interwiki link.
	 *
	 * @param string $title the title text for the link, URL-encoded (???) but
	 *   not HTML-escaped
	 * @param string $unused unused
	 * @param string $class the contents of the class attribute; if an empty
	 *   string is passed, which is the default value, defaults to 'external'.
	 * @return string
	 */
	static function getInterwikiLinkAttributes( $title, $unused = null, $class = 'external' ) {
		global $wgContLang;

		# @todo FIXME: We have a whole bunch of handling here that doesn't happen in
		# getExternalLinkAttributes, why?
		$title = urldecode( $title );
		$title = $wgContLang->checkTitleEncoding( $title );
		$title = preg_replace( '/[\\x00-\\x1f]/', ' ', $title );

		return self::getLinkAttributesInternal( $title, $class );
	}

	/**
	 * Get the appropriate HTML attributes to add to the "a" element of an internal link.
	 *
	 * @param string $title the title text for the link, URL-encoded (???) but
	 *   not HTML-escaped
	 * @param string $unused unused
	 * @param string $class the contents of the class attribute, default none
	 * @return string
	 */
	static function getInternalLinkAttributes( $title, $unused = null, $class = '' ) {
		$title = urldecode( $title );
		$title = str_replace( '_', ' ', $title );
		return self::getLinkAttributesInternal( $title, $class );
	}

	/**
	 * Get the appropriate HTML attributes to add to the "a" element of an internal
	 * link, given the Title object for the page we want to link to.
	 *
	 * @param $nt Title
	 * @param string $unused unused
	 * @param string $class the contents of the class attribute, default none
	 * @param $title Mixed: optional (unescaped) string to use in the title
	 *   attribute; if false, default to the name of the page we're linking to
	 * @return string
	 */
	static function getInternalLinkAttributesObj( $nt, $unused = null, $class = '', $title = false ) {
		if ( $title === false ) {
			$title = $nt->getPrefixedText();
		}
		return self::getLinkAttributesInternal( $title, $class );
	}

	/**
	 * Common code for getLinkAttributesX functions
	 *
	 * @param $title string
	 * @param $class string
	 *
	 * @return string
	 */
	private static function getLinkAttributesInternal( $title, $class ) {
		$title = htmlspecialchars( $title );
		$class = htmlspecialchars( $class );
		$r = '';
		if ( $class != '' ) {
			$r .= " class=\"$class\"";
		}
		if ( $title != '' ) {
			$r .= " title=\"$title\"";
		}
		return $r;
	}

	/**
	 * Return the CSS colour of a known link
	 *
	 * @param $t Title object
	 * @param $threshold Integer: user defined threshold
	 * @return String: CSS class
	 */
	public static function getLinkColour( $t, $threshold ) {
		$colour = '';
		if ( $t->isRedirect() ) {
			# Page is a redirect
			$colour = 'mw-redirect';
		} elseif ( $threshold > 0 && $t->isContentPage() &&
			$t->exists() && $t->getLength() < $threshold
		) {
			# Page is a stub
			$colour = 'stub';
		}
		return $colour;
	}

	/**
	 * This function returns an HTML link to the given target.  It serves a few
	 * purposes:
	 *   1) If $target is a Title, the correct URL to link to will be figured
	 *      out automatically.
	 *   2) It automatically adds the usual classes for various types of link
	 *      targets: "new" for red links, "stub" for short articles, etc.
	 *   3) It escapes all attribute values safely so there's no risk of XSS.
	 *   4) It provides a default tooltip if the target is a Title (the page
	 *      name of the target).
	 * link() replaces the old functions in the makeLink() family.
	 *
	 * @since 1.18 Method exists since 1.16 as non-static, made static in 1.18.
	 * You can call it using this if you want to keep compat with these:
	 * $linker = class_exists( 'DummyLinker' ) ? new DummyLinker() : new Linker();
	 * $linker->link( ... );
	 *
	 * @param $target        Title  Can currently only be a Title, but this may
	 *   change to support Images, literal URLs, etc.
	 * @param $html          string The HTML contents of the <a> element, i.e.,
	 *   the link text.  This is raw HTML and will not be escaped.  If null,
	 *   defaults to the prefixed text of the Title; or if the Title is just a
	 *   fragment, the contents of the fragment.
	 * @param array $customAttribs  A key => value array of extra HTML attributes,
	 *   such as title and class.  (href is ignored.)  Classes will be
	 *   merged with the default classes, while other attributes will replace
	 *   default attributes.  All passed attribute values will be HTML-escaped.
	 *   A false attribute value means to suppress that attribute.
	 * @param $query         array  The query string to append to the URL
	 *   you're linking to, in key => value array form.  Query keys and values
	 *   will be URL-encoded.
	 * @param string|array $options  String or array of strings:
	 *     'known': Page is known to exist, so don't check if it does.
	 *     'broken': Page is known not to exist, so don't check if it does.
	 *     'noclasses': Don't add any classes automatically (includes "new",
	 *       "stub", "mw-redirect", "extiw").  Only use the class attribute
	 *       provided, if any, so you get a simple blue link with no funny i-
	 *       cons.
	 *     'forcearticlepath': Use the article path always, even with a querystring.
	 *       Has compatibility issues on some setups, so avoid wherever possible.
	 *     'http': Force a full URL with http:// as the scheme.
	 *     'https': Force a full URL with https:// as the scheme.
	 * @return string HTML <a> attribute
	 */
	public static function link(
		$target, $html = null, $customAttribs = array(), $query = array(), $options = array()
	) {
		wfProfileIn( __METHOD__ );
		if ( !$target instanceof Title ) {
			wfProfileOut( __METHOD__ );
			return "<!-- ERROR -->$html";
		}

		if( is_string( $query ) ) {
			// some functions withing core using this still hand over query strings
			wfDeprecated( __METHOD__ . ' with parameter $query as string (should be array)', '1.20' );
			$query = wfCgiToArray( $query );
		}
		$options = (array)$options;

		$dummy = new DummyLinker; // dummy linker instance for bc on the hooks

		$ret = null;
		if ( !wfRunHooks( 'LinkBegin', array( $dummy, $target, &$html,
		&$customAttribs, &$query, &$options, &$ret ) ) ) {
			wfProfileOut( __METHOD__ );
			return $ret;
		}

		# Normalize the Title if it's a special page
		$target = self::normaliseSpecialPage( $target );

		# If we don't know whether the page exists, let's find out.
		wfProfileIn( __METHOD__ . '-checkPageExistence' );
		if ( !in_array( 'known', $options ) and !in_array( 'broken', $options ) ) {
			if ( $target->isKnown() ) {
				$options[] = 'known';
			} else {
				$options[] = 'broken';
			}
		}
		wfProfileOut( __METHOD__ . '-checkPageExistence' );

		$oldquery = array();
		if ( in_array( "forcearticlepath", $options ) && $query ) {
			$oldquery = $query;
			$query = array();
		}

		# Note: we want the href attribute first, for prettiness.
		$attribs = array( 'href' => self::linkUrl( $target, $query, $options ) );
		if ( in_array( 'forcearticlepath', $options ) && $oldquery ) {
			$attribs['href'] = wfAppendQuery( $attribs['href'], wfArrayToCgi( $oldquery ) );
		}

		$attribs = array_merge(
			$attribs,
			self::linkAttribs( $target, $customAttribs, $options )
		);
		if ( is_null( $html ) ) {
			$html = self::linkText( $target );
		}

		$ret = null;
		if ( wfRunHooks( 'LinkEnd', array( $dummy, $target, $options, &$html, &$attribs, &$ret ) ) ) {
			$ret = Html::rawElement( 'a', $attribs, $html );
		}

		wfProfileOut( __METHOD__ );
		return $ret;
	}

	/**
	 * Identical to link(), except $options defaults to 'known'.
	 * @return string
	 */
	public static function linkKnown(
		$target, $html = null, $customAttribs = array(),
		$query = array(), $options = array( 'known', 'noclasses' ) )
	{
		return self::link( $target, $html, $customAttribs, $query, $options );
	}

	/**
	 * Returns the Url used to link to a Title
	 *
	 * @param $target Title
	 * @param array $query query parameters
	 * @param $options Array
	 * @return String
	 */
	private static function linkUrl( $target, $query, $options ) {
		wfProfileIn( __METHOD__ );
		# We don't want to include fragments for broken links, because they
		# generally make no sense.
		if ( in_array( 'broken', $options ) && $target->mFragment !== '' ) {
			$target = clone $target;
			$target->mFragment = '';
		}

		# If it's a broken link, add the appropriate query pieces, unless
		# there's already an action specified, or unless 'edit' makes no sense
		# (i.e., for a nonexistent special page).
		if ( in_array( 'broken', $options ) && empty( $query['action'] )
			&& !$target->isSpecialPage() ) {
			$query['action'] = 'edit';
			$query['redlink'] = '1';
		}

		if ( in_array( 'http', $options ) ) {
			$proto = PROTO_HTTP;
		} elseif ( in_array( 'https', $options ) ) {
			$proto = PROTO_HTTPS;
		} else {
			$proto = PROTO_RELATIVE;
		}

		$ret = $target->getLinkURL( $query, false, $proto );
		wfProfileOut( __METHOD__ );
		return $ret;
	}

	/**
	 * Returns the array of attributes used when linking to the Title $target
	 *
	 * @param $target Title
	 * @param $attribs
	 * @param $options
	 *
	 * @return array
	 */
	private static function linkAttribs( $target, $attribs, $options ) {
		wfProfileIn( __METHOD__ );
		global $wgUser;
		$defaults = array();

		if ( !in_array( 'noclasses', $options ) ) {
			wfProfileIn( __METHOD__ . '-getClasses' );
			# Now build the classes.
			$classes = array();

			if ( in_array( 'broken', $options ) ) {
				$classes[] = 'new';
			}

			if ( $target->isExternal() ) {
				$classes[] = 'extiw';
			}

			if ( !in_array( 'broken', $options ) ) { # Avoid useless calls to LinkCache (see r50387)
				$colour = self::getLinkColour( $target, $wgUser->getStubThreshold() );
				if ( $colour !== '' ) {
					$classes[] = $colour; # mw-redirect or stub
				}
			}
			if ( $classes != array() ) {
				$defaults['class'] = implode( ' ', $classes );
			}
			wfProfileOut( __METHOD__ . '-getClasses' );
		}

		# Get a default title attribute.
		if ( $target->getPrefixedText() == '' ) {
			# A link like [[#Foo]].  This used to mean an empty title
			# attribute, but that's silly.  Just don't output a title.
		} elseif ( in_array( 'known', $options ) ) {
			$defaults['title'] = $target->getPrefixedText();
		} else {
			$defaults['title'] = wfMessage( 'red-link-title', $target->getPrefixedText() )->text();
		}

		# Finally, merge the custom attribs with the default ones, and iterate
		# over that, deleting all "false" attributes.
		$ret = array();
		$merged = Sanitizer::mergeAttributes( $defaults, $attribs );
		foreach ( $merged as $key => $val ) {
			# A false value suppresses the attribute, and we don't want the
			# href attribute to be overridden.
			if ( $key != 'href' and $val !== false ) {
				$ret[$key] = $val;
			}
		}
		wfProfileOut( __METHOD__ );
		return $ret;
	}

	/**
	 * Default text of the links to the Title $target
	 *
	 * @param $target Title
	 *
	 * @return string
	 */
	private static function linkText( $target ) {
		// We might be passed a non-Title by make*LinkObj().  Fail gracefully.
		if ( !$target instanceof Title ) {
			return '';
		}

		// If the target is just a fragment, with no title, we return the fragment
		// text.  Otherwise, we return the title text itself.
		if ( $target->getPrefixedText() === '' && $target->getFragment() !== '' ) {
			return htmlspecialchars( $target->getFragment() );
		}
		return htmlspecialchars( $target->getPrefixedText() );
	}

	/**
	 * Generate either a normal exists-style link or a stub link, depending
	 * on the given page size.
	 *
	 * @param $size Integer
	 * @param $nt Title object.
	 * @param $text String
	 * @param $query String
	 * @param $trail String
	 * @param $prefix String
	 * @return string HTML of link
	 * @deprecated since 1.17
	 */
	static function makeSizeLinkObj( $size, $nt, $text = '', $query = '', $trail = '', $prefix = '' ) {
		global $wgUser;
		wfDeprecated( __METHOD__, '1.17' );

		$threshold = $wgUser->getStubThreshold();
		$colour = ( $size < $threshold ) ? 'stub' : '';
		// @todo FIXME: Replace deprecated makeColouredLinkObj by link()
		return self::makeColouredLinkObj( $nt, $colour, $text, $query, $trail, $prefix );
	}

	/**
	 * Make appropriate markup for a link to the current article. This is currently rendered
	 * as the bold link text. The calling sequence is the same as the other make*LinkObj static functions,
	 * despite $query not being used.
	 *
	 * @param $nt Title
	 * @param string $html [optional]
	 * @param string $query [optional]
	 * @param string $trail [optional]
	 * @param string $prefix [optional]
	 *
	 *
	 * @return string
	 */
	public static function makeSelfLinkObj( $nt, $html = '', $query = '', $trail = '', $prefix = '' ) {
		if ( $html == '' ) {
			$html = htmlspecialchars( $nt->getPrefixedText() );
		}
		list( $inside, $trail ) = self::splitTrail( $trail );
		return "<strong class=\"selflink\">{$prefix}{$html}{$inside}</strong>{$trail}";
	}

	/**
	 * Get a message saying that an invalid title was encountered.
	 * This should be called after a method like Title::makeTitleSafe() returned
	 * a value indicating that the title object is invalid.
	 *
	 * @param $context IContextSource context to use to get the messages
	 * @param int $namespace Namespace number
	 * @param string $title Text of the title, without the namespace part
	 * @return string
	 */
	public static function getInvalidTitleDescription( IContextSource $context, $namespace, $title ) {
		global $wgContLang;

		// First we check whether the namespace exists or not.
		if ( MWNamespace::exists( $namespace ) ) {
			if ( $namespace == NS_MAIN ) {
				$name = $context->msg( 'blanknamespace' )->text();
			} else {
				$name = $wgContLang->getFormattedNsText( $namespace );
			}
			return $context->msg( 'invalidtitle-knownnamespace', $namespace, $name, $title )->text();
		} else {
			return $context->msg( 'invalidtitle-unknownnamespace', $namespace, $title )->text();
		}
	}

	/**
	 * @param $title Title
	 * @return Title
	 */
	static function normaliseSpecialPage( Title $title ) {
		if ( $title->isSpecialPage() ) {
			list( $name, $subpage ) = SpecialPageFactory::resolveAlias( $title->getDBkey() );
			if ( !$name ) {
				return $title;
			}
			$ret = SpecialPage::getTitleFor( $name, $subpage );
			$ret->mFragment = $title->getFragment();
			return $ret;
		} else {
			return $title;
		}
	}

	/**
	 * Returns the filename part of an url.
	 * Used as alternative text for external images.
	 *
	 * @param $url string
	 *
	 * @return string
	 */
	private static function fnamePart( $url ) {
		$basename = strrchr( $url, '/' );
		if ( false === $basename ) {
			$basename = $url;
		} else {
			$basename = substr( $basename, 1 );
		}
		return $basename;
	}

	/**
	 * Return the code for images which were added via external links,
	 * via Parser::maybeMakeExternalImage().
	 *
	 * @param $url
	 * @param $alt
	 *
	 * @return string
	 */
	public static function makeExternalImage( $url, $alt = '' ) {
		if ( $alt == '' ) {
			$alt = self::fnamePart( $url );
		}
		$img = '';
		$success = wfRunHooks( 'LinkerMakeExternalImage', array( &$url, &$alt, &$img ) );
		if ( !$success ) {
			wfDebug( "Hook LinkerMakeExternalImage changed the output of external image with url {$url} and alt text {$alt} to {$img}\n", true );
			return $img;
		}
		return Html::element( 'img',
			array(
				'src' => $url,
				'alt' => $alt ) );
	}

	/**
	 * Given parameters derived from [[Image:Foo|options...]], generate the
	 * HTML that that syntax inserts in the page.
	 *
	 * @param $parser Parser object
	 * @param $title Title object of the file (not the currently viewed page)
	 * @param $file File object, or false if it doesn't exist
	 * @param array $frameParams associative array of parameters external to the media handler.
	 *     Boolean parameters are indicated by presence or absence, the value is arbitrary and
	 *     will often be false.
	 *          thumbnail       If present, downscale and frame
	 *          manualthumb     Image name to use as a thumbnail, instead of automatic scaling
	 *          framed          Shows image in original size in a frame
	 *          frameless       Downscale but don't frame
	 *          upright         If present, tweak default sizes for portrait orientation
	 *          upright_factor  Fudge factor for "upright" tweak (default 0.75)
	 *          border          If present, show a border around the image
	 *          align           Horizontal alignment (left, right, center, none)
	 *          valign          Vertical alignment (baseline, sub, super, top, text-top, middle,
	 *                          bottom, text-bottom)
	 *          alt             Alternate text for image (i.e. alt attribute). Plain text.
	 *          class           HTML for image classes. Plain text.
	 *          caption         HTML for image caption.
	 *          link-url        URL to link to
	 *          link-title      Title object to link to
	 *          link-target     Value for the target attribute, only with link-url
	 *          no-link         Boolean, suppress description link
	 *
	 * @param array $handlerParams associative array of media handler parameters, to be passed
	 *       to transform(). Typical keys are "width" and "page".
	 * @param string $time timestamp of the file, set as false for current
	 * @param string $query query params for desc url
	 * @param $widthOption: Used by the parser to remember the user preference thumbnailsize
	 * @since 1.20
	 * @return String: HTML for an image, with links, wrappers, etc.
	 */
	public static function makeImageLink( /*Parser*/ $parser, Title $title, $file, $frameParams = array(),
		$handlerParams = array(), $time = false, $query = "", $widthOption = null )
	{
		$res = null;
		$dummy = new DummyLinker;
		if ( !wfRunHooks( 'ImageBeforeProduceHTML', array( &$dummy, &$title,
			&$file, &$frameParams, &$handlerParams, &$time, &$res ) ) ) {
			return $res;
		}

		if ( $file && !$file->allowInlineDisplay() ) {
			wfDebug( __METHOD__ . ': ' . $title->getPrefixedDBkey() . " does not allow inline display\n" );
			return self::link( $title );
		}

		// Shortcuts
		$fp =& $frameParams;
		$hp =& $handlerParams;

		// Clean up parameters
		$page = isset( $hp['page'] ) ? $hp['page'] : false;
		if ( !isset( $fp['align'] ) ) {
			$fp['align'] = '';
		}
		if ( !isset( $fp['alt'] ) ) {
			$fp['alt'] = '';
		}
		if ( !isset( $fp['title'] ) ) {
			$fp['title'] = '';
		}
		if ( !isset( $fp['class'] ) ) {
			$fp['class'] = '';
		}

		$prefix = $postfix = '';

		if ( 'center' == $fp['align'] ) {
			$prefix = '<div class="center">';
			$postfix = '</div>';
			$fp['align'] = 'none';
		}
		if ( $file && !isset( $hp['width'] ) ) {
			if ( isset( $hp['height'] ) && $file->isVectorized() ) {
				// If its a vector image, and user only specifies height
				// we don't want it to be limited by its "normal" width.
				global $wgSVGMaxSize;
				$hp['width'] = $wgSVGMaxSize;
			} else {
				$hp['width'] = $file->getWidth( $page );
			}

			if ( isset( $fp['thumbnail'] ) || isset( $fp['framed'] ) || isset( $fp['frameless'] ) || !$hp['width'] ) {
				global $wgThumbLimits, $wgThumbUpright;
				if ( $widthOption === null || !isset( $wgThumbLimits[$widthOption] ) ) {
					$widthOption = User::getDefaultOption( 'thumbsize' );
				}

				// Reduce width for upright images when parameter 'upright' is used
				if ( isset( $fp['upright'] ) && $fp['upright'] == 0 ) {
					$fp['upright'] = $wgThumbUpright;
				}
				// For caching health: If width scaled down due to upright parameter, round to full __0 pixel to avoid the creation of a lot of odd thumbs
				$prefWidth = isset( $fp['upright'] ) ?
					round( $wgThumbLimits[$widthOption] * $fp['upright'], -1 ) :
					$wgThumbLimits[$widthOption];

				// Use width which is smaller: real image width or user preference width
				// Unless image is scalable vector.
				if ( !isset( $hp['height'] ) && ( $hp['width'] <= 0 ||
						$prefWidth < $hp['width'] || $file->isVectorized() ) ) {
					$hp['width'] = $prefWidth;
				}
			}
		}

		if ( isset( $fp['thumbnail'] ) || isset( $fp['manualthumb'] ) || isset( $fp['framed'] ) ) {
			# Create a thumbnail. Alignment depends on the writing direction of
			# the page content language (right-aligned for LTR languages,
			# left-aligned for RTL languages)
			#
			# If a thumbnail width has not been provided, it is set
			# to the default user option as specified in Language*.php
			if ( $fp['align'] == '' ) {
				if( $parser instanceof Parser ) {
					$fp['align'] = $parser->getTargetLanguage()->alignEnd();
				} else {
					# backwards compatibility, remove with makeImageLink2()
					global $wgContLang;
					$fp['align'] = $wgContLang->alignEnd();
				}
			}
			return $prefix . self::makeThumbLink2( $title, $file, $fp, $hp, $time, $query ) . $postfix;
		}

		if ( $file && isset( $fp['frameless'] ) ) {
			$srcWidth = $file->getWidth( $page );
			# For "frameless" option: do not present an image bigger than the source (for bitmap-style images)
			# This is the same behavior as the "thumb" option does it already.
			if ( $srcWidth && !$file->mustRender() && $hp['width'] > $srcWidth ) {
				$hp['width'] = $srcWidth;
			}
		}

		if ( $file && isset( $hp['width'] ) ) {
			# Create a resized image, without the additional thumbnail features
			$thumb = $file->transform( $hp );
		} else {
			$thumb = false;
		}

		if ( !$thumb ) {
			$s = self::makeBrokenImageLinkObj( $title, $fp['title'], '', '', '', $time == true );
		} else {
			self::processResponsiveImages( $file, $thumb, $hp );
			$params = array(
				'alt' => $fp['alt'],
				'title' => $fp['title'],
				'valign' => isset( $fp['valign'] ) ? $fp['valign'] : false,
				'img-class' => $fp['class'] );
			if ( isset( $fp['border'] ) ) {
				// TODO: BUG? Both values are identical
				$params['img-class'] .= ( $params['img-class'] !== '' ) ? ' thumbborder' : 'thumbborder';
			}
			$params = self::getImageLinkMTOParams( $fp, $query, $parser ) + $params;

			$s = $thumb->toHtml( $params );
		}
		if ( $fp['align'] != '' ) {
			$s = "<div class=\"float{$fp['align']}\">{$s}</div>";
		}
		return str_replace( "\n", ' ', $prefix . $s . $postfix );
	}

	/**
	 * See makeImageLink()
	 * When this function is removed, remove if( $parser instanceof Parser ) check there too
	 * @deprecated since 1.20
	 */
	public static function makeImageLink2( Title $title, $file, $frameParams = array(),
		$handlerParams = array(), $time = false, $query = "", $widthOption = null ) {
		return self::makeImageLink( null, $title, $file, $frameParams,
			$handlerParams, $time, $query, $widthOption );
	}

	/**
	 * Get the link parameters for MediaTransformOutput::toHtml() from given
	 * frame parameters supplied by the Parser.
	 * @param array $frameParams The frame parameters
	 * @param string $query An optional query string to add to description page links
	 * @return array
	 */
	private static function getImageLinkMTOParams( $frameParams, $query = '', $parser = null ) {
		$mtoParams = array();
		if ( isset( $frameParams['link-url'] ) && $frameParams['link-url'] !== '' ) {
			$mtoParams['custom-url-link'] = $frameParams['link-url'];
			if ( isset( $frameParams['link-target'] ) ) {
				$mtoParams['custom-target-link'] = $frameParams['link-target'];
			}
			if ( $parser ) {
				$extLinkAttrs = $parser->getExternalLinkAttribs( $frameParams['link-url'] );
				foreach ( $extLinkAttrs as $name => $val ) {
					// Currently could include 'rel' and 'target'
					$mtoParams['parser-extlink-' . $name] = $val;
				}
			}
		} elseif ( isset( $frameParams['link-title'] ) && $frameParams['link-title'] !== '' ) {
			$mtoParams['custom-title-link'] = self::normaliseSpecialPage( $frameParams['link-title'] );
		} elseif ( !empty( $frameParams['no-link'] ) ) {
			// No link
		} else {
			$mtoParams['desc-link'] = true;
			$mtoParams['desc-query'] = $query;
		}
		return $mtoParams;
	}

	/**
	 * Make HTML for a thumbnail including image, border and caption
	 * @param $title Title object
	 * @param $file File object or false if it doesn't exist
	 * @param $label String
	 * @param $alt String
	 * @param $align String
	 * @param $params Array
	 * @param $framed Boolean
	 * @param $manualthumb String
	 * @return mixed
	 */
	public static function makeThumbLinkObj( Title $title, $file, $label = '', $alt,
		$align = 'right', $params = array(), $framed = false, $manualthumb = "" )
	{
		$frameParams = array(
			'alt' => $alt,
			'caption' => $label,
			'align' => $align
		);
		if ( $framed ) {
			$frameParams['framed'] = true;
		}
		if ( $manualthumb ) {
			$frameParams['manualthumb'] = $manualthumb;
		}
		return self::makeThumbLink2( $title, $file, $frameParams, $params );
	}

	/**
	 * @param $title Title
	 * @param  $file File
	 * @param array $frameParams
	 * @param array $handlerParams
	 * @param bool $time
	 * @param string $query
	 * @return mixed
	 */
	public static function makeThumbLink2( Title $title, $file, $frameParams = array(),
		$handlerParams = array(), $time = false, $query = "" )
	{
		global $wgStylePath, $wgContLang;
		$exists = $file && $file->exists();

		# Shortcuts
		$fp =& $frameParams;
		$hp =& $handlerParams;

		$page = isset( $hp['page'] ) ? $hp['page'] : false;
		if ( !isset( $fp['align'] ) ) $fp['align'] = 'right';
		if ( !isset( $fp['alt'] ) ) $fp['alt'] = '';
		if ( !isset( $fp['title'] ) ) $fp['title'] = '';
		if ( !isset( $fp['caption'] ) ) $fp['caption'] = '';

		if ( empty( $hp['width'] ) ) {
			// Reduce width for upright images when parameter 'upright' is used
			$hp['width'] = isset( $fp['upright'] ) ? 130 : 180;
		}
		$thumb = false;
		$noscale = false;

		if ( !$exists ) {
			$outerWidth = $hp['width'] + 2;
		} else {
			if ( isset( $fp['manualthumb'] ) ) {
				# Use manually specified thumbnail
				$manual_title = Title::makeTitleSafe( NS_FILE, $fp['manualthumb'] );
				if ( $manual_title ) {
					$manual_img = wfFindFile( $manual_title );
					if ( $manual_img ) {
						$thumb = $manual_img->getUnscaledThumb( $hp );
					} else {
						$exists = false;
					}
				}
			} elseif ( isset( $fp['framed'] ) ) {
				// Use image dimensions, don't scale
				$thumb = $file->getUnscaledThumb( $hp );
				$noscale = true;
			} else {
				# Do not present an image bigger than the source, for bitmap-style images
				# This is a hack to maintain compatibility with arbitrary pre-1.10 behavior
				$srcWidth = $file->getWidth( $page );
				if ( $srcWidth && !$file->mustRender() && $hp['width'] > $srcWidth ) {
					$hp['width'] = $srcWidth;
				}
				$thumb = $file->transform( $hp );
			}

			if ( $thumb ) {
				$outerWidth = $thumb->getWidth() + 2;
			} else {
				$outerWidth = $hp['width'] + 2;
			}
		}

		# ThumbnailImage::toHtml() already adds page= onto the end of DjVu URLs
		# So we don't need to pass it here in $query. However, the URL for the
		# zoom icon still needs it, so we make a unique query for it. See bug 14771
		$url = $title->getLocalURL( $query );
		if ( $page ) {
			$url = wfAppendQuery( $url, 'page=' . urlencode( $page ) );
		}

		$s = "<div class=\"thumb t{$fp['align']}\"><div class=\"thumbinner\" style=\"width:{$outerWidth}px;\">";
		if ( !$exists ) {
			$s .= self::makeBrokenImageLinkObj( $title, $fp['title'], '', '', '', $time == true );
			$zoomIcon = '';
		} elseif ( !$thumb ) {
			$s .= wfMessage( 'thumbnail_error', '' )->escaped();
			$zoomIcon = '';
		} else {
			if ( !$noscale ) {
				self::processResponsiveImages( $file, $thumb, $hp );
			}
			$params = array(
				'alt' => $fp['alt'],
				'title' => $fp['title'],
				'img-class' => ( isset( $fp['class'] ) && $fp['class'] !== '' ) ? $fp['class'] . ' thumbimage' : 'thumbimage'
			);
			$params = self::getImageLinkMTOParams( $fp, $query ) + $params;
			$s .= $thumb->toHtml( $params );
			if ( isset( $fp['framed'] ) ) {
				$zoomIcon = "";
			} else {
				$zoomIcon = Html::rawElement( 'div', array( 'class' => 'magnify' ),
					Html::rawElement( 'a', array(
						'href' => $url,
						'class' => 'internal',
						'title' => wfMessage( 'thumbnail-more' )->text() ),
						Html::element( 'img', array(
							'src' => $wgStylePath . '/common/images/magnify-clip' . ( $wgContLang->isRTL() ? '-rtl' : '' ) . '.png',
							'width' => 15,
							'height' => 11,
							'alt' => "" ) ) ) );
			}
		}
		$s .= '  <div class="thumbcaption">' . $zoomIcon . $fp['caption'] . "</div></div></div>";
		return str_replace( "\n", ' ', $s );
	}

	/**
	 * Process responsive images: add 1.5x and 2x subimages to the thumbnail, where
	 * applicable.
	 *
	 * @param File $file
	 * @param MediaOutput $thumb
	 * @param array $hp image parameters
	 */
	protected static function processResponsiveImages( $file, $thumb, $hp ) {
		global $wgResponsiveImages;
		if ( $wgResponsiveImages ) {
			$hp15 = $hp;
			$hp15['width'] = round( $hp['width'] * 1.5 );
			$hp20 = $hp;
			$hp20['width'] = $hp['width'] * 2;
			if ( isset( $hp['height'] ) ) {
				$hp15['height'] = round( $hp['height'] * 1.5 );
				$hp20['height'] = $hp['height'] * 2;
			}

			$thumb15 = $file->transform( $hp15 );
			$thumb20 = $file->transform( $hp20 );
			if ( $thumb15->url !== $thumb->url ) {
				$thumb->responsiveUrls['1.5'] = $thumb15->url;
			}
			if ( $thumb20->url !== $thumb->url ) {
				$thumb->responsiveUrls['2'] = $thumb20->url;
			}
		}
	}

	/**
	 * Make a "broken" link to an image
	 *
	 * @param $title Title object
	 * @param string $label link label (plain text)
	 * @param string $query query string
	 * @param $unused1 Unused parameter kept for b/c
	 * @param $unused2 Unused parameter kept for b/c
	 * @param $time Boolean: a file of a certain timestamp was requested
	 * @return String
	 */
	public static function makeBrokenImageLinkObj( $title, $label = '', $query = '', $unused1 = '', $unused2 = '', $time = false ) {
		global $wgEnableUploads, $wgUploadMissingFileUrl, $wgUploadNavigationUrl;
		if ( ! $title instanceof Title ) {
			return "<!-- ERROR -->" . htmlspecialchars( $label );
		}
		wfProfileIn( __METHOD__ );
		if ( $label == '' ) {
			$label = $title->getPrefixedText();
		}
		$encLabel = htmlspecialchars( $label );
		$currentExists = $time ? ( wfFindFile( $title ) != false ) : false;

		if ( ( $wgUploadMissingFileUrl || $wgUploadNavigationUrl || $wgEnableUploads ) && !$currentExists ) {
			$redir = RepoGroup::singleton()->getLocalRepo()->checkRedirect( $title );

			if ( $redir ) {
				wfProfileOut( __METHOD__ );
				return self::linkKnown( $title, $encLabel, array(), wfCgiToArray( $query ) );
			}

			$href = self::getUploadUrl( $title, $query );

			wfProfileOut( __METHOD__ );
			return '<a href="' . htmlspecialchars( $href ) . '" class="new" title="' .
				htmlspecialchars( $title->getPrefixedText(), ENT_QUOTES ) . '">' .
				$encLabel . '</a>';
		}

		wfProfileOut( __METHOD__ );
		return self::linkKnown( $title, $encLabel, array(), wfCgiToArray( $query ) );
	}

	/**
	 * Get the URL to upload a certain file
	 *
	 * @param $destFile Title object of the file to upload
	 * @param string $query urlencoded query string to prepend
	 * @return String: urlencoded URL
	 */
	protected static function getUploadUrl( $destFile, $query = '' ) {
		global $wgUploadMissingFileUrl, $wgUploadNavigationUrl;
		$q = 'wpDestFile=' . $destFile->getPartialUrl();
		if ( $query != '' )
			$q .= '&' . $query;

		if ( $wgUploadMissingFileUrl ) {
			return wfAppendQuery( $wgUploadMissingFileUrl, $q );
		} elseif( $wgUploadNavigationUrl ) {
			return wfAppendQuery( $wgUploadNavigationUrl, $q );
		} else {
			$upload = SpecialPage::getTitleFor( 'Upload' );
			return $upload->getLocalUrl( $q );
		}
	}

	/**
	 * Create a direct link to a given uploaded file.
	 *
	 * @param $title Title object.
	 * @param string $html pre-sanitized HTML
	 * @param string $time MW timestamp of file creation time
	 * @return String: HTML
	 */
	public static function makeMediaLinkObj( $title, $html = '', $time = false ) {
		$img = wfFindFile( $title, array( 'time' => $time ) );
		return self::makeMediaLinkFile( $title, $img, $html );
	}

	/**
	 * Create a direct link to a given uploaded file.
	 * This will make a broken link if $file is false.
	 *
	 * @param $title Title object.
	 * @param $file File|bool mixed File object or false
	 * @param string $html pre-sanitized HTML
	 * @return String: HTML
	 *
	 * @todo Handle invalid or missing images better.
	 */
	public static function makeMediaLinkFile( Title $title, $file, $html = '' ) {
		if ( $file && $file->exists() ) {
			$url = $file->getURL();
			$class = 'internal';
		} else {
			$url = self::getUploadUrl( $title );
			$class = 'new';
		}
		$alt = htmlspecialchars( $title->getText(), ENT_QUOTES );
		if ( $html == '' ) {
			$html = $alt;
		}
		$u = htmlspecialchars( $url );
		return "<a href=\"{$u}\" class=\"$class\" title=\"{$alt}\">{$html}</a>";
	}

	/**
	 * Make a link to a special page given its name and, optionally,
	 * a message key from the link text.
	 * Usage example: Linker::specialLink( 'Recentchanges' )
	 *
	 * @return string
	 */
	public static function specialLink( $name, $key = '' ) {
		if ( $key == '' ) {
			$key = strtolower( $name );
		}

		return self::linkKnown( SpecialPage::getTitleFor( $name ), wfMessage( $key )->text() );
	}

	/**
	 * Make an external link
	 * @param string $url URL to link to
	 * @param string $text text of link
	 * @param $escape Boolean: do we escape the link text?
	 * @param string $linktype type of external link. Gets added to the classes
	 * @param array $attribs of extra attributes to <a>
	 * @param $title Title|null Title object used for title specific link attributes
	 * @return string
	 */
	public static function makeExternalLink( $url, $text, $escape = true, $linktype = '', $attribs = array(), $title = null ) {
		global $wgTitle;
		$class = "external";
		if ( $linktype ) {
			$class .= " $linktype";
		}
		if ( isset( $attribs['class'] ) && $attribs['class'] ) {
			$class .= " {$attribs['class']}";
		}
		$attribs['class'] = $class;

		if ( $escape ) {
			$text = htmlspecialchars( $text );
		}

		if ( !$title ) {
			$title = $wgTitle;
		}
		$attribs['rel'] = Parser::getExternalLinkRel( $url, $title );
		$link = '';
		$success = wfRunHooks( 'LinkerMakeExternalLink',
			array( &$url, &$text, &$link, &$attribs, $linktype ) );
		if ( !$success ) {
			wfDebug( "Hook LinkerMakeExternalLink changed the output of link with url {$url} and text {$text} to {$link}\n", true );
			return $link;
		}
		$attribs['href'] = $url;
		return Html::rawElement( 'a', $attribs, $text );
	}

	/**
	 * Make user link (or user contributions for unregistered users)
	 * @param $userId   Integer: user id in database.
	 * @param string $userName user name in database.
	 * @param string $altUserName text to display instead of the user name (optional)
	 * @return String: HTML fragment
	 * @since 1.19 Method exists for a long time. $altUserName was added in 1.19.
	 */
	public static function userLink( $userId, $userName, $altUserName = false ) {
		if ( $userId == 0 ) {
			$page = SpecialPage::getTitleFor( 'Contributions', $userName );
			if ( $altUserName === false ) {
				$altUserName = IP::prettifyIP( $userName );
			}
		} else {
			$page = Title::makeTitle( NS_USER, $userId );
		}

		return self::link(
			$page,
			htmlspecialchars( $altUserName !== false ? $altUserName : $userName ),
			array( 'class' => 'mw-userlink' )
		);
	}

	/**
	 * Generate standard user tool links (talk, contributions, block link, etc.)
	 *
	 * @param $userId Integer: user identifier
	 * @param string $userText user name or IP address
	 * @param $redContribsWhenNoEdits Boolean: should the contributions link be
	 *        red if the user has no edits?
	 * @param $flags Integer: customisation flags (e.g. Linker::TOOL_LINKS_NOBLOCK and Linker::TOOL_LINKS_EMAIL)
	 * @param $edits Integer: user edit count (optional, for performance)
	 * @return String: HTML fragment
	 */
	public static function userToolLinks(
		$userId, $userText, $redContribsWhenNoEdits = false, $flags = 0, $edits = null
	) {
		global $wgUser, $wgDisableAnonTalk, $wgLang;
		$talkable = !( $wgDisableAnonTalk && 0 == $userId );
		$blockable = !( $flags & self::TOOL_LINKS_NOBLOCK );
		$addEmailLink = $flags & self::TOOL_LINKS_EMAIL && $userId;

		$items = array();
		if ( $talkable ) {
			$items[] = self::userTalkLink( $userId, $userText );
		}
		if ( $userId ) {
			// check if the user has an edit
			$attribs = array();
			if ( $redContribsWhenNoEdits ) {
				if ( intval( $edits ) === 0 && $edits !== 0 ) {
					$user = User::newFromId( $userId );
					$edits = $user->getEditCount();
				}
				if ( $edits === 0 ) {
					$attribs['class'] = 'new';
				}
			}
			$contribsPage = SpecialPage::getTitleFor( 'Contributions', $userText );

			$items[] = self::link( $contribsPage, wfMessage( 'contribslink' )->escaped(), $attribs );
		}
		if ( $blockable && $wgUser->isAllowed( 'block' ) ) {
			$items[] = self::blockLink( $userId, $userText );
		}

		if ( $addEmailLink && $wgUser->canSendEmail() ) {
			$items[] = self::emailLink( $userId, $userText );
		}

		wfRunHooks( 'UserToolLinksEdit', array( $userId, $userText, &$items ) );

		if ( $items ) {
			return wfMessage( 'word-separator' )->plain()
				. '<span class="mw-usertoollinks">'
				. wfMessage( 'parentheses' )->rawParams( $wgLang->pipeList( $items ) )->escaped()
				. '</span>';
		} else {
			return '';
		}
	}

	/**
	 * Alias for userToolLinks( $userId, $userText, true );
	 * @param $userId Integer: user identifier
	 * @param string $userText user name or IP address
	 * @param $edits Integer: user edit count (optional, for performance)
	 * @return String
	 */
	public static function userToolLinksRedContribs( $userId, $userText, $edits = null ) {
		return self::userToolLinks( $userId, $userText, true, 0, $edits );
	}

	/**
	 * @param $userId Integer: user id in database.
	 * @param string $userText user name in database.
	 * @return String: HTML fragment with user talk link
	 */
	public static function userTalkLink( $userId, $userText ) {
		$userTalkPage = Title::makeTitle( NS_USER_TALK, $userText );
		$userTalkLink = self::link( $userTalkPage, wfMessage( 'talkpagelinktext' )->escaped() );
		return $userTalkLink;
	}

	/**
	 * @param $userId Integer: userid
	 * @param string $userText user name in database.
	 * @return String: HTML fragment with block link
	 */
	public static function blockLink( $userId, $userText ) {
		$blockPage = SpecialPage::getTitleFor( 'Block', $userText );
		$blockLink = self::link( $blockPage, wfMessage( 'blocklink' )->escaped() );
		return $blockLink;
	}

	/**
	 * @param $userId Integer: userid
	 * @param string $userText user name in database.
	 * @return String: HTML fragment with e-mail user link
	 */
	public static function emailLink( $userId, $userText ) {
		$emailPage = SpecialPage::getTitleFor( 'Emailuser', $userText );
		$emailLink = self::link( $emailPage, wfMessage( 'emaillink' )->escaped() );
		return $emailLink;
	}

	/**
	 * Generate a user link if the current user is allowed to view it
	 * @param $rev Revision object.
	 * @param $isPublic Boolean: show only if all users can see it
	 * @return String: HTML fragment
	 */
	public static function revUserLink( $rev, $isPublic = false ) {
		if ( $rev->isDeleted( Revision::DELETED_USER ) && $isPublic ) {
			$link = wfMessage( 'rev-deleted-user' )->escaped();
		} elseif ( $rev->userCan( Revision::DELETED_USER ) ) {
			$link = self::userLink( $rev->getUser( Revision::FOR_THIS_USER ),
				$rev->getUserText( Revision::FOR_THIS_USER ) );
		} else {
			$link = wfMessage( 'rev-deleted-user' )->escaped();
		}
		if ( $rev->isDeleted( Revision::DELETED_USER ) ) {
			return '<span class="history-deleted">' . $link . '</span>';
		}
		return $link;
	}

	/**
	 * Generate a user tool link cluster if the current user is allowed to view it
	 * @param $rev Revision object.
	 * @param $isPublic Boolean: show only if all users can see it
	 * @return string HTML
	 */
	public static function revUserTools( $rev, $isPublic = false ) {
		if ( $rev->isDeleted( Revision::DELETED_USER ) && $isPublic ) {
			$link = wfMessage( 'rev-deleted-user' )->escaped();
		} elseif ( $rev->userCan( Revision::DELETED_USER ) ) {
			$userId = $rev->getUser( Revision::FOR_THIS_USER );
			$userText = $rev->getUserText( Revision::FOR_THIS_USER );
			$link = self::userLink( $userId, $userText )
				. wfMessage( 'word-separator' )->plain()
				. self::userToolLinks( $userId, $userText );
		} else {
			$link = wfMessage( 'rev-deleted-user' )->escaped();
		}
		if ( $rev->isDeleted( Revision::DELETED_USER ) ) {
			return ' <span class="history-deleted">' . $link . '</span>';
		}
		return $link;
	}

	/**
	 * This function is called by all recent changes variants, by the page history,
	 * and by the user contributions list. It is responsible for formatting edit
	 * summaries. It escapes any HTML in the summary, but adds some CSS to format
	 * auto-generated comments (from section editing) and formats [[wikilinks]].
	 *
	 * @author Erik Moeller <moeller@scireview.de>
	 *
	 * Note: there's not always a title to pass to this function.
	 * Since you can't set a default parameter for a reference, I've turned it
	 * temporarily to a value pass. Should be adjusted further. --brion
	 *
	 * @param $comment String
	 * @param $title Mixed: Title object (to generate link to the section in autocomment) or null
	 * @param $local Boolean: whether section links should refer to local page
	 * @return mixed|String
	 */
	public static function formatComment( $comment, $title = null, $local = false ) {
		wfProfileIn( __METHOD__ );

		# Sanitize text a bit:
		$comment = str_replace( "\n", " ", $comment );
		# Allow HTML entities (for bug 13815)
		$comment = Sanitizer::escapeHtmlAllowEntities( $comment );

		# Render autocomments and make links:
		$comment = self::formatAutocomments( $comment, $title, $local );
		$comment = self::formatLinksInComment( $comment, $title, $local );

		wfProfileOut( __METHOD__ );
		return $comment;
	}

	/**
	 * @var Title
	 */
	static $autocommentTitle;
	static $autocommentLocal;

	/**
	 * Converts autogenerated comments in edit summaries into section links.
	 * The pattern for autogen comments is / * foo * /, which makes for
	 * some nasty regex.
	 * We look for all comments, match any text before and after the comment,
	 * add a separator where needed and format the comment itself with CSS
	 * Called by Linker::formatComment.
	 *
	 * @param string $comment comment text
	 * @param $title Title|null An optional title object used to links to sections
	 * @param $local Boolean: whether section links should refer to local page
	 * @return String: formatted comment
	 */
	private static function formatAutocomments( $comment, $title = null, $local = false ) {
		// Bah!
		self::$autocommentTitle = $title;
		self::$autocommentLocal = $local;
		$comment = preg_replace_callback(
			'!(.*)/\*\s*(.*?)\s*\*/(.*)!',
			array( 'Linker', 'formatAutocommentsCallback' ),
			$comment );
		self::$autocommentTitle = null;
		self::$autocommentLocal = null;
		return $comment;
	}

	/**
	 * Helper function for Linker::formatAutocomments
	 * @param $match
	 * @return string
	 */
	private static function formatAutocommentsCallback( $match ) {
		global $wgLang;
		$title = self::$autocommentTitle;
		$local = self::$autocommentLocal;

		$pre = $match[1];
		$auto = $match[2];
		$post = $match[3];
		$comment = null;
		wfRunHooks( 'FormatAutocomments', array( &$comment, $pre, $auto, $post, $title, $local ) );
		if ( $comment === null ) {
			$link = '';
			if ( $title ) {
				$section = $auto;

				# Remove links that a user may have manually put in the autosummary
				# This could be improved by copying as much of Parser::stripSectionName as desired.
				$section = str_replace( '[[:', '', $section );
				$section = str_replace( '[[', '', $section );
				$section = str_replace( ']]', '', $section );

				$section = Sanitizer::normalizeSectionNameWhitespace( $section ); # bug 22784
				if ( $local ) {
					$sectionTitle = Title::newFromText( '#' . $section );
				} else {
					$sectionTitle = Title::makeTitleSafe( $title->getNamespace(),
						$title->getDBkey(), $section );
				}
				if ( $sectionTitle ) {
					$link = self::link( $sectionTitle,
						$wgLang->getArrow(), array(), array(),
						'noclasses' );
				} else {
					$link = '';
				}
			}
			if ( $pre ) {
				# written summary $presep autocomment (summary /* section */)
				$pre .= wfMessage( 'autocomment-prefix' )->inContentLanguage()->escaped();
			}
			if ( $post ) {
				# autocomment $postsep written summary (/* section */ summary)
				$auto .= wfMessage( 'colon-separator' )->inContentLanguage()->escaped();
			}
			$auto = '<span class="autocomment">' . $auto . '</span>';
			$comment = $pre . $link . $wgLang->getDirMark() . '<span dir="auto">' . $auto . $post . '</span>';
		}
		return $comment;
	}

	/**
	 * @var Title
	 */
	static $commentContextTitle;
	static $commentLocal;

	/**
	 * Formats wiki links and media links in text; all other wiki formatting
	 * is ignored
	 *
	 * @todo FIXME: Doesn't handle sub-links as in image thumb texts like the main parser
	 * @param string $comment text to format links in
	 * @param $title Title|null An optional title object used to links to sections
	 * @param $local Boolean: whether section links should refer to local page
	 * @return String
	 */
	public static function formatLinksInComment( $comment, $title = null, $local = false ) {
		self::$commentContextTitle = $title;
		self::$commentLocal = $local;
		$html = preg_replace_callback(
			'/
				\[\[
				:? # ignore optional leading colon
				([^\]|]+) # 1. link target; page names cannot include ] or |
				(?:\|
					# 2. a pipe-separated substring; only the last is captured
					# Stop matching at | and ]] without relying on backtracking.
					((?:]?[^\]|])*+)
				)*
				\]\]
				([^[]*) # 3. link trail (the text up until the next link)
			/x',
			array( 'Linker', 'formatLinksInCommentCallback' ),
			$comment );
		self::$commentContextTitle = null;
		self::$commentLocal = null;
		return $html;
	}

	/**
	 * @param $match
	 * @return mixed
	 */
	protected static function formatLinksInCommentCallback( $match ) {
		global $wgContLang;

		$medians = '(?:' . preg_quote( MWNamespace::getCanonicalName( NS_MEDIA ), '/' ) . '|';
		$medians .= preg_quote( $wgContLang->getNsText( NS_MEDIA ), '/' ) . '):';

		$comment = $match[0];

		# fix up urlencoded title texts (copied from Parser::replaceInternalLinks)
		if ( strpos( $match[1], '%' ) !== false ) {
			$match[1] = str_replace( array( '<', '>' ), array( '&lt;', '&gt;' ), rawurldecode( $match[1] ) );
		}

		# Handle link renaming [[foo|text]] will show link as "text"
		if ( $match[2] != "" ) {
			$text = $match[2];
		} else {
			$text = $match[1];
		}
		$submatch = array();
		$thelink = null;
		if ( preg_match( '/^' . $medians . '(.*)$/i', $match[1], $submatch ) ) {
			# Media link; trail not supported.
			$linkRegexp = '/\[\[(.*?)\]\]/';
			$title = Title::makeTitleSafe( NS_FILE, $submatch[1] );
			if ( $title ) {
				$thelink = self::makeMediaLinkObj( $title, $text );
			}
		} else {
			# Other kind of link
			if ( preg_match( $wgContLang->linkTrail(), $match[3], $submatch ) ) {
				$trail = $submatch[1];
			} else {
				$trail = "";
			}
			$linkRegexp = '/\[\[(.*?)\]\]' . preg_quote( $trail, '/' ) . '/';
			if ( isset( $match[1][0] ) && $match[1][0] == ':' )
				$match[1] = substr( $match[1], 1 );
			list( $inside, $trail ) = self::splitTrail( $trail );

			$linkText = $text;
			$linkTarget = self::normalizeSubpageLink( self::$commentContextTitle,
				$match[1], $linkText );

			$target = Title::newFromText( $linkTarget );
			if ( $target ) {
				if ( $target->getText() == '' && $target->getInterwiki() === ''
					&& !self::$commentLocal && self::$commentContextTitle )
				{
					$newTarget = clone ( self::$commentContextTitle );
					$newTarget->setFragment( '#' . $target->getFragment() );
					$target = $newTarget;
				}
				$thelink = self::link(
					$target,
					$linkText . $inside
				) . $trail;
			}
		}
		if ( $thelink ) {
			// If the link is still valid, go ahead and replace it in!
			$comment = preg_replace( $linkRegexp, StringUtils::escapeRegexReplacement( $thelink ), $comment, 1 );
		}

		return $comment;
	}

	/**
	 * @param $contextTitle Title
	 * @param  $target
	 * @param  $text
	 * @return string
	 */
	public static function normalizeSubpageLink( $contextTitle, $target, &$text ) {
		# Valid link forms:
		# Foobar -- normal
		# :Foobar -- override special treatment of prefix (images, language links)
		# /Foobar -- convert to CurrentPage/Foobar
		# /Foobar/ -- convert to CurrentPage/Foobar, strip the initial / from text
		# ../ -- convert to CurrentPage, from CurrentPage/CurrentSubPage
		# ../Foobar -- convert to CurrentPage/Foobar, from CurrentPage/CurrentSubPage

		wfProfileIn( __METHOD__ );
		$ret = $target; # default return value is no change

		# Some namespaces don't allow subpages,
		# so only perform processing if subpages are allowed
		if ( $contextTitle && MWNamespace::hasSubpages( $contextTitle->getNamespace() ) ) {
			$hash = strpos( $target, '#' );
			if ( $hash !== false ) {
				$suffix = substr( $target, $hash );
				$target = substr( $target, 0, $hash );
			} else {
				$suffix = '';
			}
			# bug 7425
			$target = trim( $target );
			# Look at the first character
			if ( $target != '' && $target[0] === '/' ) {
				# / at end means we don't want the slash to be shown
				$m = array();
				$trailingSlashes = preg_match_all( '%(/+)$%', $target, $m );
				if ( $trailingSlashes ) {
					$noslash = $target = substr( $target, 1, -strlen( $m[0][0] ) );
				} else {
					$noslash = substr( $target, 1 );
				}

				$ret = $contextTitle->getPrefixedText() . '/' . trim( $noslash ) . $suffix;
				if ( $text === '' ) {
					$text = $target . $suffix;
				} # this might be changed for ugliness reasons
			} else {
				# check for .. subpage backlinks
				$dotdotcount = 0;
				$nodotdot = $target;
				while ( strncmp( $nodotdot, "../", 3 ) == 0 ) {
					++$dotdotcount;
					$nodotdot = substr( $nodotdot, 3 );
				}
				if ( $dotdotcount > 0 ) {
					$exploded = explode( '/', $contextTitle->GetPrefixedText() );
					if ( count( $exploded ) > $dotdotcount ) { # not allowed to go below top level page
						$ret = implode( '/', array_slice( $exploded, 0, -$dotdotcount ) );
						# / at the end means don't show full path
						if ( substr( $nodotdot, -1, 1 ) === '/' ) {
							$nodotdot = substr( $nodotdot, 0, -1 );
							if ( $text === '' ) {
								$text = $nodotdot . $suffix;
							}
						}
						$nodotdot = trim( $nodotdot );
						if ( $nodotdot != '' ) {
							$ret .= '/' . $nodotdot;
						}
						$ret .= $suffix;
					}
				}
			}
		}

		wfProfileOut( __METHOD__ );
		return $ret;
	}

	/**
	 * Wrap a comment in standard punctuation and formatting if
	 * it's non-empty, otherwise return empty string.
	 *
	 * @param $comment String
	 * @param $title Mixed: Title object (to generate link to section in autocomment) or null
	 * @param $local Boolean: whether section links should refer to local page
	 *
	 * @return string
	 */
	public static function commentBlock( $comment, $title = null, $local = false ) {
		// '*' used to be the comment inserted by the software way back
		// in antiquity in case none was provided, here for backwards
		// compatibility, acc. to brion -ævar
		if ( $comment == '' || $comment == '*' ) {
			return '';
		} else {
			$formatted = self::formatComment( $comment, $title, $local );
			$formatted = wfMessage( 'parentheses' )->rawParams( $formatted )->escaped();
			return " <span class=\"comment\">$formatted</span>";
		}
	}

	/**
	 * Wrap and format the given revision's comment block, if the current
	 * user is allowed to view it.
	 *
	 * @param $rev Revision object
	 * @param $local Boolean: whether section links should refer to local page
	 * @param $isPublic Boolean: show only if all users can see it
	 * @return String: HTML fragment
	 */
	public static function revComment( Revision $rev, $local = false, $isPublic = false ) {
		if ( $rev->getRawComment() == "" ) {
			return "";
		}
		if ( $rev->isDeleted( Revision::DELETED_COMMENT ) && $isPublic ) {
			$block = " <span class=\"comment\">" . wfMessage( 'rev-deleted-comment' )->escaped() . "</span>";
		} elseif ( $rev->userCan( Revision::DELETED_COMMENT ) ) {
			$block = self::commentBlock( $rev->getComment( Revision::FOR_THIS_USER ),
				$rev->getTitle(), $local );
		} else {
			$block = " <span class=\"comment\">" . wfMessage( 'rev-deleted-comment' )->escaped() . "</span>";
		}
		if ( $rev->isDeleted( Revision::DELETED_COMMENT ) ) {
			return " <span class=\"history-deleted\">$block</span>";
		}
		return $block;
	}

	/**
	 * @param $size
	 * @return string
	 */
	public static function formatRevisionSize( $size ) {
		if ( $size == 0 ) {
			$stxt = wfMessage( 'historyempty' )->escaped();
		} else {
			$stxt = wfMessage( 'nbytes' )->numParams( $size )->escaped();
			$stxt = wfMessage( 'parentheses' )->rawParams( $stxt )->escaped();
		}
		return "<span class=\"history-size\">$stxt</span>";
	}

	/**
	 * Add another level to the Table of Contents
	 *
	 * @return string
	 */
	public static function tocIndent() {
		return "\n<ul>";
	}

	/**
	 * Finish one or more sublevels on the Table of Contents
	 *
	 * @return string
	 */
	public static function tocUnindent( $level ) {
		return "</li>\n" . str_repeat( "</ul>\n</li>\n", $level > 0 ? $level : 0 );
	}

	/**
	 * parameter level defines if we are on an indentation level
	 *
	 * @return string
	 */
	public static function tocLine( $anchor, $tocline, $tocnumber, $level, $sectionIndex = false ) {
		$classes = "toclevel-$level";
		if ( $sectionIndex !== false ) {
			$classes .= " tocsection-$sectionIndex";
		}
		return "\n<li class=\"$classes\"><a href=\"#" .
			$anchor . '"><span class="tocnumber">' .
			$tocnumber . '</span> <span class="toctext">' .
			$tocline . '</span></a>';
	}

	/**
	 * End a Table Of Contents line.
	 * tocUnindent() will be used instead if we're ending a line below
	 * the new level.
	 * @return string
	 */
	public static function tocLineEnd() {
		return "</li>\n";
	}

	/**
	 * Wraps the TOC in a table and provides the hide/collapse javascript.
	 *
	 * @param string $toc html of the Table Of Contents
	 * @param $lang String|Language|false: Language for the toc title, defaults to user language
	 * @return String: full html of the TOC
	 */
	public static function tocList( $toc, $lang = false ) {
		$lang = wfGetLangObj( $lang );
		$title = wfMessage( 'toc' )->inLanguage( $lang )->escaped();

		return '<table id="toc" class="toc"><tr><td>'
			. '<div id="toctitle"><h2>' . $title . "</h2></div>\n"
			. $toc
			. "</ul>\n</td></tr></table>\n";
	}

	/**
	 * Generate a table of contents from a section tree
	 * Currently unused.
	 *
	 * @param array $tree Return value of ParserOutput::getSections()
	 * @return String: HTML fragment
	 */
	public static function generateTOC( $tree ) {
		$toc = '';
		$lastLevel = 0;
		foreach ( $tree as $section ) {
			if ( $section['toclevel'] > $lastLevel )
				$toc .= self::tocIndent();
			elseif ( $section['toclevel'] < $lastLevel )
				$toc .= self::tocUnindent(
					$lastLevel - $section['toclevel'] );
			else
				$toc .= self::tocLineEnd();

			$toc .= self::tocLine( $section['anchor'],
				$section['line'], $section['number'],
				$section['toclevel'], $section['index'] );
			$lastLevel = $section['toclevel'];
		}
		$toc .= self::tocLineEnd();
		return self::tocList( $toc );
	}

	/**
	 * Create a headline for content
	 *
	 * @param $level Integer: the level of the headline (1-6)
	 * @param string $attribs any attributes for the headline, starting with
	 *                 a space and ending with '>'
	 *                 This *must* be at least '>' for no attribs
	 * @param string $anchor the anchor to give the headline (the bit after the #)
	 * @param string $html html for the text of the header
	 * @param string $link HTML to add for the section edit link
	 * @param $legacyAnchor Mixed: a second, optional anchor to give for
	 *   backward compatibility (false to omit)
	 *
	 * @return String: HTML headline
	 */
	public static function makeHeadline( $level, $attribs, $anchor, $html, $link, $legacyAnchor = false ) {
		$ret = "<h$level$attribs"
			. $link
			. " <span class=\"mw-headline\" id=\"$anchor\">$html</span>"
			. "</h$level>";
		if ( $legacyAnchor !== false ) {
			$ret = "<div id=\"$legacyAnchor\"></div>$ret";
		}
		return $ret;
	}

	/**
	 * Split a link trail, return the "inside" portion and the remainder of the trail
	 * as a two-element array
	 * @return array
	 */
	static function splitTrail( $trail ) {
		global $wgContLang;
		$regex = $wgContLang->linkTrail();
		$inside = '';
		if ( $trail !== '' ) {
			$m = array();
			if ( preg_match( $regex, $trail, $m ) ) {
				$inside = $m[1];
				$trail = $m[2];
			}
		}
		return array( $inside, $trail );
	}

	/**
	 * Generate a rollback link for a given revision.  Currently it's the
	 * caller's responsibility to ensure that the revision is the top one. If
	 * it's not, of course, the user will get an error message.
	 *
	 * If the calling page is called with the parameter &bot=1, all rollback
	 * links also get that parameter. It causes the edit itself and the rollback
	 * to be marked as "bot" edits. Bot edits are hidden by default from recent
	 * changes, so this allows sysops to combat a busy vandal without bothering
	 * other users.
	 *
	 * If the option verify is set this function will return the link only in case the
	 * revision can be reverted. Please note that due to performance limitations
	 * it might be assumed that a user isn't the only contributor of a page while
	 * (s)he is, which will lead to useless rollback links. Furthermore this wont
	 * work if $wgShowRollbackEditCount is disabled, so this can only function
	 * as an additional check.
	 *
	 * If the option noBrackets is set the rollback link wont be enclosed in []
	 *
	 * @param $rev Revision object
	 * @param $context IContextSource context to use or null for the main context.
	 * @param $options array
	 * @return string
	 */
	public static function generateRollback( $rev, IContextSource $context = null, $options = array( 'verify' ) ) {
		if ( $context === null ) {
			$context = RequestContext::getMain();
		}
		$editCount = false;
		if ( in_array( 'verify', $options ) ) {
			$editCount = self::getRollbackEditCount( $rev, true );
			if ( $editCount === false ) {
				return '';
			}
		}

		$inner = self::buildRollbackLink( $rev, $context, $editCount );

		if ( !in_array( 'noBrackets', $options ) ) {
			$inner = $context->msg( 'brackets' )->rawParams( $inner )->plain();
		}

		return '<span class="mw-rollback-link">' . $inner . '</span>';
	}

	/**
	 * This function will return the number of revisions which a rollback
	 * would revert and, if $verify is set it will verify that a revision
	 * can be reverted (that the user isn't the only contributor and the
	 * revision we might rollback to isn't deleted). These checks can only
	 * function as an additional check as this function only checks against
	 * the last $wgShowRollbackEditCount edits.
	 *
	 * Returns null if $wgShowRollbackEditCount is disabled or false if $verify
	 * is set and the user is the only contributor of the page.
	 *
	 * @param $rev Revision object
	 * @param bool $verify Try to verify that this revision can really be rolled back
	 * @return integer|bool|null
	 */
	public static function getRollbackEditCount( $rev, $verify ) {
		global $wgShowRollbackEditCount;
		if ( !is_int( $wgShowRollbackEditCount ) || !$wgShowRollbackEditCount > 0 ) {
			// Nothing has happened, indicate this by returning 'null'
			return null;
		}

		$dbr = wfGetDB( DB_SLAVE );

		// Up to the value of $wgShowRollbackEditCount revisions are counted
		$res = $dbr->select(
			'revision',
			array( 'rev_user_text', 'rev_deleted' ),
			// $rev->getPage() returns null sometimes
			array( 'rev_page' => $rev->getTitle()->getArticleID() ),
			__METHOD__,
			array(
				'USE INDEX' => array( 'revision' => 'page_timestamp' ),
				'ORDER BY' => 'rev_timestamp DESC',
				'LIMIT' => $wgShowRollbackEditCount + 1
			)
		);

		$editCount = 0;
		$moreRevs = false;
		foreach ( $res as $row ) {
			if ( $rev->getRawUserText() != $row->rev_user_text ) {
				if ( $verify && ( $row->rev_deleted & Revision::DELETED_TEXT || $row->rev_deleted & Revision::DELETED_USER ) ) {
					// If the user or the text of the revision we might rollback to is deleted in some way we can't rollback
					// Similar to the sanity checks in WikiPage::commitRollback
					return false;
				}
				$moreRevs = true;
				break;
			}
			$editCount++;
		}

		if ( $verify && $editCount <= $wgShowRollbackEditCount && !$moreRevs ) {
			// We didn't find at least $wgShowRollbackEditCount revisions made by the current user
			// and there weren't any other revisions. That means that the current user is the only
			// editor, so we can't rollback
			return false;
		}
		return $editCount;
	}

	/**
	 * Build a raw rollback link, useful for collections of "tool" links
	 *
	 * @param $rev Revision object
	 * @param $context IContextSource context to use or null for the main context.
	 * @param $editCount integer Number of edits that would be reverted
	 * @return String: HTML fragment
	 */
	public static function buildRollbackLink( $rev, IContextSource $context = null, $editCount = false ) {
		global $wgShowRollbackEditCount, $wgMiserMode;

		// To config which pages are effected by miser mode
		$disableRollbackEditCountSpecialPage = array( 'Recentchanges', 'Watchlist' );

		if ( $context === null ) {
			$context = RequestContext::getMain();
		}

		$title = $rev->getTitle();
		$query = array(
			'action' => 'rollback',
			'from' => $rev->getUserText(),
			'token' => $context->getUser()->getEditToken( array( $title->getPrefixedText(), $rev->getUserText() ) ),
		);
		if ( $context->getRequest()->getBool( 'bot' ) ) {
			$query['bot'] = '1';
			$query['hidediff'] = '1'; // bug 15999
		}

		$disableRollbackEditCount = false;
		if( $wgMiserMode ) {
			foreach( $disableRollbackEditCountSpecialPage as $specialPage ) {
				if( $context->getTitle()->isSpecial( $specialPage ) ) {
					$disableRollbackEditCount = true;
					break;
				}
			}
		}

		if( !$disableRollbackEditCount && is_int( $wgShowRollbackEditCount ) && $wgShowRollbackEditCount > 0 ) {
			if ( !is_numeric( $editCount ) ) {
				$editCount = self::getRollbackEditCount( $rev, false );
			}

			if( $editCount > $wgShowRollbackEditCount ) {
				$editCount_output = $context->msg( 'rollbacklinkcount-morethan' )->numParams( $wgShowRollbackEditCount )->parse();
			} else {
				$editCount_output = $context->msg( 'rollbacklinkcount' )->numParams( $editCount )->parse();
			}

			return self::link(
				$title,
				$editCount_output,
				array( 'title' => $context->msg( 'tooltip-rollback' )->text() ),
				$query,
				array( 'known', 'noclasses' )
			);
		} else {
			return self::link(
				$title,
				$context->msg( 'rollbacklink' )->escaped(),
				array( 'title' => $context->msg( 'tooltip-rollback' )->text() ),
				$query,
				array( 'known', 'noclasses' )
			);
		}
	}

	/**
	 * Returns HTML for the "templates used on this page" list.
	 *
	 * Make an HTML list of templates, and then add a "More..." link at
	 * the bottom. If $more is null, do not add a "More..." link. If $more
	 * is a Title, make a link to that title and use it. If $more is a string,
	 * directly paste it in as the link (escaping needs to be done manually).
	 * Finally, if $more is a Message, call toString().
	 *
	 * @param array $templates Array of templates from Article::getUsedTemplate or similar
	 * @param bool $preview Whether this is for a preview
	 * @param bool $section Whether this is for a section edit
	 * @param Title|Message|string|null $more An escaped link for "More..." of the templates
	 * @return String: HTML output
	 */
	public static function formatTemplates( $templates, $preview = false, $section = false, $more = null ) {
		wfProfileIn( __METHOD__ );

		$outText = '';
		if ( count( $templates ) > 0 ) {
			# Do a batch existence check
			$batch = new LinkBatch;
			foreach ( $templates as $title ) {
				$batch->addObj( $title );
			}
			$batch->execute();

			# Construct the HTML
			$outText = '<div class="mw-templatesUsedExplanation">';
			if ( $preview ) {
				$outText .= wfMessage( 'templatesusedpreview' )->numParams( count( $templates ) )
					->parseAsBlock();
			} elseif ( $section ) {
				$outText .= wfMessage( 'templatesusedsection' )->numParams( count( $templates ) )
					->parseAsBlock();
			} else {
				$outText .= wfMessage( 'templatesused' )->numParams( count( $templates ) )
					->parseAsBlock();
			}
			$outText .= "</div><ul>\n";

			usort( $templates, 'Title::compare' );
			foreach ( $templates as $titleObj ) {
				$r = $titleObj->getRestrictions( 'edit' );
				if ( in_array( 'sysop', $r ) ) {
					$protected = wfMessage( 'template-protected' )->parse();
				} elseif ( in_array( 'autoconfirmed', $r ) ) {
					$protected = wfMessage( 'template-semiprotected' )->parse();
				} else {
					$protected = '';
				}
				if ( $titleObj->quickUserCan( 'edit' ) ) {
					$editLink = self::link(
						$titleObj,
						wfMessage( 'editlink' )->text(),
						array(),
						array( 'action' => 'edit' )
					);
				} else {
					$editLink = self::link(
						$titleObj,
						wfMessage( 'viewsourcelink' )->text(),
						array(),
						array( 'action' => 'edit' )
					);
				}
				$outText .= '<li>' . self::link( $titleObj )
					. wfMessage( 'word-separator' )->escaped()
					. wfMessage( 'parentheses' )->rawParams( $editLink )->escaped()
					. wfMessage( 'word-separator' )->escaped()
					. $protected . '</li>';
			}

			if ( $more instanceof Title ) {
				$outText .= '<li>' . self::link( $more, wfMessage( 'moredotdotdot' ) ) . '</li>';
			} elseif ( $more ) {
				$outText .= "<li>$more</li>";
			}

			$outText .= '</ul>';
		}
		wfProfileOut( __METHOD__ );
		return $outText;
	}

	/**
	 * Returns HTML for the "hidden categories on this page" list.
	 *
	 * @param array $hiddencats of hidden categories from Article::getHiddenCategories
	 * or similar
	 * @return String: HTML output
	 */
	public static function formatHiddenCategories( $hiddencats ) {
		wfProfileIn( __METHOD__ );

		$outText = '';
		if ( count( $hiddencats ) > 0 ) {
			# Construct the HTML
			$outText = '<div class="mw-hiddenCategoriesExplanation">';
			$outText .= wfMessage( 'hiddencategories' )->numParams( count( $hiddencats ) )->parseAsBlock();
			$outText .= "</div><ul>\n";

			foreach ( $hiddencats as $titleObj ) {
				$outText .= '<li>' . self::link( $titleObj, null, array(), array(), 'known' ) . "</li>\n"; # If it's hidden, it must exist - no need to check with a LinkBatch
			}
			$outText .= '</ul>';
		}
		wfProfileOut( __METHOD__ );
		return $outText;
	}

	/**
	 * Format a size in bytes for output, using an appropriate
	 * unit (B, KB, MB or GB) according to the magnitude in question
	 *
	 * @param int $size Size to format
	 * @return String
	 */
	public static function formatSize( $size ) {
		global $wgLang;
		return htmlspecialchars( $wgLang->formatSize( $size ) );
	}

	/**
	 * Given the id of an interface element, constructs the appropriate title
	 * attribute from the system messages.  (Note, this is usually the id but
	 * isn't always, because sometimes the accesskey needs to go on a different
	 * element than the id, for reverse-compatibility, etc.)
	 *
	 * @param string $name id of the element, minus prefixes.
	 * @param $options Mixed: null or the string 'withaccess' to add an access-
	 *   key hint
	 * @return String: contents of the title attribute (which you must HTML-
	 *   escape), or false for no title attribute
	 */
	public static function titleAttrib( $name, $options = null ) {
		wfProfileIn( __METHOD__ );

		$message = wfMessage( "tooltip-$name" );

		if ( !$message->exists() ) {
			$tooltip = false;
		} else {
			$tooltip = $message->text();
			# Compatibility: formerly some tooltips had [alt-.] hardcoded
			$tooltip = preg_replace( "/ ?\[alt-.\]$/", '', $tooltip );
			# Message equal to '-' means suppress it.
			if ( $tooltip == '-' ) {
				$tooltip = false;
			}
		}

		if ( $options == 'withaccess' ) {
			$accesskey = self::accesskey( $name );
			if ( $accesskey !== false ) {
				if ( $tooltip === false || $tooltip === '' ) {
					$tooltip = "[$accesskey]";
				} else {
					$tooltip .= " [$accesskey]";
				}
			}
		}

		wfProfileOut( __METHOD__ );
		return $tooltip;
	}

	static $accesskeycache;

	/**
	 * Given the id of an interface element, constructs the appropriate
	 * accesskey attribute from the system messages.  (Note, this is usually
	 * the id but isn't always, because sometimes the accesskey needs to go on
	 * a different element than the id, for reverse-compatibility, etc.)
	 *
	 * @param string $name id of the element, minus prefixes.
	 * @return String: contents of the accesskey attribute (which you must HTML-
	 *   escape), or false for no accesskey attribute
	 */
	public static function accesskey( $name ) {
		if ( isset( self::$accesskeycache[$name] ) ) {
			return self::$accesskeycache[$name];
		}
		wfProfileIn( __METHOD__ );

		$message = wfMessage( "accesskey-$name" );

		if ( !$message->exists() ) {
			$accesskey = false;
		} else {
			$accesskey = $message->plain();
			if ( $accesskey === '' || $accesskey === '-' ) {
				# @todo FIXME: Per standard MW behavior, a value of '-' means to suppress the
				# attribute, but this is broken for accesskey: that might be a useful
				# value.
				$accesskey = false;
			}
		}

		wfProfileOut( __METHOD__ );
		return self::$accesskeycache[$name] = $accesskey;
	}

	/**
	 * Get a revision-deletion link, or disabled link, or nothing, depending
	 * on user permissions & the settings on the revision.
	 *
	 * Will use forward-compatible revision ID in the Special:RevDelete link
	 * if possible, otherwise the timestamp-based ID which may break after
	 * undeletion.
	 *
	 * @param User $user
	 * @param Revision $rev
	 * @param Revision $title
	 * @return string HTML fragment
	 */
	public static function getRevDeleteLink( User $user, Revision $rev, Title $title ) {
		$canHide = $user->isAllowed( 'deleterevision' );
		if ( !$canHide && !( $rev->getVisibility() && $user->isAllowed( 'deletedhistory' ) ) ) {
			return '';
		}

		if ( !$rev->userCan( Revision::DELETED_RESTRICTED, $user ) ) {
			return Linker::revDeleteLinkDisabled( $canHide ); // revision was hidden from sysops
		} else {
			if ( $rev->getId() ) {
				// RevDelete links using revision ID are stable across
				// page deletion and undeletion; use when possible.
				$query = array(
					'type' => 'revision',
					'target' => $title->getPrefixedDBkey(),
					'ids' => $rev->getId()
				);
			} else {
				// Older deleted entries didn't save a revision ID.
				// We have to refer to these by timestamp, ick!
				$query = array(
					'type' => 'archive',
					'target' => $title->getPrefixedDBkey(),
					'ids' => $rev->getTimestamp()
				);
			}
			return Linker::revDeleteLink( $query,
				$rev->isDeleted( Revision::DELETED_RESTRICTED ), $canHide );
		}
	}

	/**
	 * Creates a (show/hide) link for deleting revisions/log entries
	 *
	 * @param array $query query parameters to be passed to link()
	 * @param $restricted Boolean: set to true to use a "<strong>" instead of a "<span>"
	 * @param $delete Boolean: set to true to use (show/hide) rather than (show)
	 *
	 * @return String: HTML "<a>" link to Special:Revisiondelete, wrapped in a
	 * span to allow for customization of appearance with CSS
	 */
	public static function revDeleteLink( $query = array(), $restricted = false, $delete = true ) {
		$sp = SpecialPage::getTitleFor( 'Revisiondelete' );
		$msgKey = $delete ? 'rev-delundel' : 'rev-showdeleted';
		$html = wfMessage( $msgKey )->escaped();
		$tag = $restricted ? 'strong' : 'span';
		$link = self::link( $sp, $html, array(), $query, array( 'known', 'noclasses' ) );
		return Xml::tags( $tag, array( 'class' => 'mw-revdelundel-link' ), wfMessage( 'parentheses' )->rawParams( $link )->escaped() );
	}

	/**
	 * Creates a dead (show/hide) link for deleting revisions/log entries
	 *
	 * @param $delete Boolean: set to true to use (show/hide) rather than (show)
	 *
	 * @return string HTML text wrapped in a span to allow for customization
	 * of appearance with CSS
	 */
	public static function revDeleteLinkDisabled( $delete = true ) {
		$msgKey = $delete ? 'rev-delundel' : 'rev-showdeleted';
		$html = wfMessage( $msgKey )->escaped();
		$htmlParentheses = wfMessage( 'parentheses' )->rawParams( $html )->escaped();
		return Xml::tags( 'span', array( 'class' => 'mw-revdelundel-link' ), $htmlParentheses );
	}

	/* Deprecated methods */

	/**
	 * @deprecated since 1.16 Use link()
	 *
	 * This function is a shortcut to makeBrokenLinkObj(Title::newFromText($title),...). Do not call
	 * it if you already have a title object handy. See makeBrokenLinkObj for further documentation.
	 *
	 * @param string $title The text of the title
	 * @param string $text Link text
	 * @param string $query Optional query part
	 * @param string $trail Optional trail. Alphabetic characters at the start of this string will
	 *               be included in the link text. Other characters will be appended after
	 *               the end of the link.
	 * @return string
	 */
	static function makeBrokenLink( $title, $text = '', $query = '', $trail = '' ) {
		wfDeprecated( __METHOD__, '1.16' );

		$nt = Title::newFromText( $title );
		if ( $nt instanceof Title ) {
			return self::makeBrokenLinkObj( $nt, $text, $query, $trail );
		} else {
			wfDebug( 'Invalid title passed to self::makeBrokenLink(): "' . $title . "\"\n" );
			return $text == '' ? $title : $text;
		}
	}

	/**
	 * @deprecated since 1.16 Use link(); warnings since 1.21
	 *
	 * Make a link for a title which may or may not be in the database. If you need to
	 * call this lots of times, pre-fill the link cache with a LinkBatch, otherwise each
	 * call to this will result in a DB query.
	 *
	 * @param $nt     Title: the title object to make the link from, e.g. from
	 *                      Title::newFromText.
	 * @param $text  String: link text
	 * @param string $query optional query part
	 * @param string $trail optional trail. Alphabetic characters at the start of this string will
	 *                      be included in the link text. Other characters will be appended after
	 *                      the end of the link.
	 * @param string $prefix optional prefix. As trail, only before instead of after.
	 * @return string
	 */
	static function makeLinkObj( $nt, $text = '', $query = '', $trail = '', $prefix = '' ) {
		wfDeprecated( __METHOD__, '1.21' );

		wfProfileIn( __METHOD__ );
		$query = wfCgiToArray( $query );
		list( $inside, $trail ) = self::splitTrail( $trail );
		if ( $text === '' ) {
			$text = self::linkText( $nt );
		}

		$ret = self::link( $nt, "$prefix$text$inside", array(), $query ) . $trail;

		wfProfileOut( __METHOD__ );
		return $ret;
	}

	/**
	 * @deprecated since 1.16 Use link(); warnings since 1.21
	 *
	 * Make a link for a title which definitely exists. This is faster than makeLinkObj because
	 * it doesn't have to do a database query. It's also valid for interwiki titles and special
	 * pages.
	 *
	 * @param $title  Title object of target page
	 * @param $text   String: text to replace the title
	 * @param $query  String: link target
	 * @param $trail  String: text after link
	 * @param string $prefix text before link text
	 * @param string $aprops extra attributes to the a-element
	 * @param $style  String: style to apply - if empty, use getInternalLinkAttributesObj instead
	 * @return string the a-element
	 */
	static function makeKnownLinkObj(
		$title, $text = '', $query = '', $trail = '', $prefix = '', $aprops = '', $style = ''
	) {
		wfDeprecated( __METHOD__, '1.21' );

		wfProfileIn( __METHOD__ );

		if ( $text == '' ) {
			$text = self::linkText( $title );
		}
		$attribs = Sanitizer::mergeAttributes(
			Sanitizer::decodeTagAttributes( $aprops ),
			Sanitizer::decodeTagAttributes( $style )
		);
		$query = wfCgiToArray( $query );
		list( $inside, $trail ) = self::splitTrail( $trail );

		$ret = self::link( $title, "$prefix$text$inside", $attribs, $query,
			array( 'known', 'noclasses' ) ) . $trail;

		wfProfileOut( __METHOD__ );
		return $ret;
	}

	/**
	 * @deprecated since 1.16 Use link()
	 *
	 * Make a red link to the edit page of a given title.
	 *
	 * @param $title Title object of the target page
	 * @param $text  String: Link text
	 * @param string $query Optional query part
	 * @param string $trail Optional trail. Alphabetic characters at the start of this string will
	 *                      be included in the link text. Other characters will be appended after
	 *                      the end of the link.
	 * @param string $prefix Optional prefix
	 * @return string
	 */
	static function makeBrokenLinkObj( $title, $text = '', $query = '', $trail = '', $prefix = '' ) {
		wfDeprecated( __METHOD__, '1.16' );

		wfProfileIn( __METHOD__ );

		list( $inside, $trail ) = self::splitTrail( $trail );
		if ( $text === '' ) {
			$text = self::linkText( $title );
		}

		$ret = self::link( $title, "$prefix$text$inside", array(),
			wfCgiToArray( $query ), 'broken' ) . $trail;

		wfProfileOut( __METHOD__ );
		return $ret;
	}

	/**
	 * @deprecated since 1.16 Use link()
	 *
	 * Make a coloured link.
	 *
	 * @param $nt Title object of the target page
	 * @param $colour Integer: colour of the link
	 * @param $text   String:  link text
	 * @param $query  String:  optional query part
	 * @param $trail  String:  optional trail. Alphabetic characters at the start of this string will
	 *                      be included in the link text. Other characters will be appended after
	 *                      the end of the link.
	 * @param string $prefix Optional prefix
	 * @return string
	 */
	static function makeColouredLinkObj( $nt, $colour, $text = '', $query = '', $trail = '', $prefix = '' ) {
		wfDeprecated( __METHOD__, '1.16' );

		if ( $colour != '' ) {
			$style = self::getInternalLinkAttributesObj( $nt, $text, $colour );
		} else {
			$style = '';
		}
		return self::makeKnownLinkObj( $nt, $text, $query, $trail, $prefix, '', $style );
	}

	/**
	 * Returns the attributes for the tooltip and access key.
	 * @return array
	 */
	public static function tooltipAndAccesskeyAttribs( $name ) {
		# @todo FIXME: If Sanitizer::expandAttributes() treated "false" as "output
		# no attribute" instead of "output '' as value for attribute", this
		# would be three lines.
		$attribs = array(
			'title' => self::titleAttrib( $name, 'withaccess' ),
			'accesskey' => self::accesskey( $name )
		);
		if ( $attribs['title'] === false ) {
			unset( $attribs['title'] );
		}
		if ( $attribs['accesskey'] === false ) {
			unset( $attribs['accesskey'] );
		}
		return $attribs;
	}

	/**
	 * Returns raw bits of HTML, use titleAttrib()
	 * @return null|string
	 */
	public static function tooltip( $name, $options = null ) {
		# @todo FIXME: If Sanitizer::expandAttributes() treated "false" as "output
		# no attribute" instead of "output '' as value for attribute", this
		# would be two lines.
		$tooltip = self::titleAttrib( $name, $options );
		if ( $tooltip === false ) {
			return '';
		}
		return Xml::expandAttributes( array(
			'title' => $tooltip
		) );
	}
}

/**
 * @since 1.18
 */
class DummyLinker {

	/**
	 * Use PHP's magic __call handler to transform instance calls to a dummy instance
	 * into static calls to the new Linker for backwards compatibility.
	 *
	 * @param string $fname Name of called method
	 * @param array $args Arguments to the method
	 * @return mixed
	 */
	public function __call( $fname, $args ) {
		return call_user_func_array( array( 'Linker', $fname ), $args );
	}
}
