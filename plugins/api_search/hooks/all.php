<?php

function HookApi_searchAllAdditionaljoins()
    {
    global $api_search_include_fields;
    $joins=array_unique($api_search_include_fields);
    return $joins;
    }
