<?php

interface EventStreamInterface extends EventEmitterInterface {

  /**
   * maps events using $fn. if $fn is a function,
   * apply it to the incoming data to generate
   * outgoing data. if $fn is a string starting with
   * a dot, take it as a property name to disassemble
   * incoming events. in any other case use $fn as
   * an constant
   *
   * @param mixed $fn
   * @return EventStreamInterface
   */

  public function map($fn);

  /**
   * Expects a function that produces EventStreams
   * from the input Stream, all of which will be
   * merged into the returned output stream.
   *
   * @param callable $fn a function that returns EventStreams
   * @return EventStreamInterface
   */

  public function flatMap(callable $fn);

  /**
   * Expects a function that produces EventStreams
   * from the input Stream, only the last of which
   * will be piping into the returned output stream
   *
   * @param callable $fn a function that returns EventStreams
   * @return EventStreamInterface
   */

  public function flatMapLatest(callable $fn);

  /**
   * pipe one stream directly into another
   *
   * @param EventStreamInterface
   * @return self
   */

  public function pipe(EventStreamInterface $stream);
}
