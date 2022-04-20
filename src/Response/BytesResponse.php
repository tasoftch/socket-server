<?php


namespace TASoft\Server\Response;


class BytesResponse extends AbstractDataResponse
{
	/**
	 * @inheritDoc
	 */
	public function getResponse()
	{
		return join("", array_map(function($byte) { return chr($byte); }, $this->getData()));
	}
}