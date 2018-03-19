<?php
use Slothsoft\Farah\Stream\FarahWrapper;

stream_wrapper_register('farah', FarahWrapper::class);