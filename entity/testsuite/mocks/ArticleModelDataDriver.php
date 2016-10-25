<?php

/* mockup for the data driver */
class ArticleModelDataDriver extends InMemoryDataDriver
{
  public function getArticlesWithIDInRange( $lo, $hi )
  {

    $out = array();

    foreach ( $this->data as $row )
    {
        if ( $row['id'] >= $lo && $row['id'] <= $hi )
        {
          $out[] = $row;
        }
    }

    return $out;

  }

  public function getArticlesWithOddIds()
  {

    $out = array();

    foreach ( $this->data as $row )
    {
        if ( $row['id'] % 2 != 0 )
        {
          $out[] = $row;
        }
    }

    return $out;
  }
}

