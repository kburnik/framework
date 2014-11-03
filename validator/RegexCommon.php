<?
class RegexCommon {

  public static
        $ENTRY = '/^(?!.{33,})([A-Za-zČčĆćĐđŠšŽž-]{2,}){1,}/u'
      , $NON_EMPTY_TEXT = '/^.{3,}(.*)/u'
      , $ONE = '/^[1]$/u'
      , $EMPTY_OR_ONE = '/^(|1)$/u'
      , $ALPHANUMERIC = '/^([A-Za-z0-9ČčĆćĐđŠšŽž-]{2,}){1,}/u'
      , $NAME = '/^([ ]{0,}[A-Za-zČčĆćĐđŠšŽž-]{2,}[ ]{0,}){1,}$/u'
      , $ADDRESS = '/^(?!.{33,})(([^ ]{2,})([ ]{1,}))([^ ]{1,})/u'
      , $PHONE = '/^[0-9 \-\/\(\)\+]{8,16}$/u'
      , $NUMBER = '/^[0-9 ]{5,12}$/u'
      , $EMPTY_OR_OIB = '/^(|[0-9]{10,15})$/u'
      , $MAIL = '/([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\][a-zA-Z0-9\-]+\.)+))([a-zA-Z]([a-zA-Z]{1,3}|[0-9]{1,3}))(\]?)/'
      , $USERNAME = '/^[A-Za-z_.\@\-0-9]{4,32}$/u'
      , $PASSWORD = '/^[A-Za-z_.\@\-0-9]{6,}$/u'
      , $EMPTY_OR_PASSWORD = '/^(|[A-Za-z_.\@\-0-9]{6,})$/u'
      , $ANYTHING = '/^(.*)$/u'
      , $INTEGER = '/^\s*-?[0-9]{1,10}\s*$/u'
      , $PRICE = '/^\s*-?[0-9]{1,10}(,[0-9]{1,2}){0,1}\s*(.*)$/u'
      , $EMPTY_OR_PRICE = '/^\s*(|-?[0-9]{1,10}(,[0-9]{1,2}){0,1}\s*(.*))$/u'

  ;
}

?>