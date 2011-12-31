<?php
/**
 * ConsoleResponse
 *
 * @package    agavi
 * @subpackage response
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Felix Gilcher <felix@andersground.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.RC4
 *
 * @version    $Id$
 */
class ConsoleResponse extends AgaviResponse
{
	/**
	 * Send all response data to the client.
	 *
	 * @param      AgaviOutputType An optional Output Type object with information
	 *                             the response can use to send additional data,
	 *                             such as HTTP headers
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function send(AgaviOutputType $outputType = null)
	{
		$this->sendContent();
	}
	
	/**
	 * Clear all reponse data.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->clearContent();
	}

	
	/**
	 * Import response data from another response.
	 *
	 * @param      AgaviResponse The other response to import information from.
	 *
	 * @author     Felix Gilcher <felix@andersground.net>
	 * @since      0.11.RC4
	 */
	public function merge(AgaviResponse $otherResponse)
	{
	}

	/**
	 * Redirect externally.
	 *
	 * @param      mixed Where to redirect.
	 *
	 * @author     Felix Gilcher <felix@andersground.net>
	 * @since      0.11.RC4
	 */
	public function setRedirect($to)
	{
		throw new BadMethodCallException('Redirects are not implemented for Console.');
	}

	/**
	 * Get info about the set redirect. Not implemented here.
	 *
	 * @return     array An assoc array of redirect info, or null if none set.
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRedirect()
	{
		throw new BadMethodCallException('Redirects are not implemented for Console.');
	}

	/**
	 * Check if a redirect is set. Not implemented here.
	 *
	 * @return     bool true, if a redirect is set, otherwise falsae
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasRedirect()
	{
		throw new BadMethodCallException('Redirects are not implemented for Console.');
	}

	/**
	 * Clear any set redirect information. Not implemented here.
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearRedirect()
	{
		throw new BadMethodCallException('Redirects are not implemented for Console.');
	}

	/**
	 * @see        AgaviResponse::isMutable()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isContentMutable()
	{
		return false;
	}
}
?>
