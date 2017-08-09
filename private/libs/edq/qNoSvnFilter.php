<?php
  class qNoSvnFilter extends RecursiveFilterIterator
  {
    public static $FILTERS = array(
      '.svn',
    );

    public function accept()
    {
      return !in_array($this->current()->getFilename(), self::$FILTERS, true);
    }
  }
