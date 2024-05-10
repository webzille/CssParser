<?php

namespace Webzille\CssParser\Enum;

enum State: string
{
    case Root = "Root";
    case AtRule = "AtRule";
    case Selector = "Selector";
    case Property = "Property";
    case PropertyValue = "PropertyValue";
    case Comment = "Comment";

    public function label(): string
    {
        return match($this) {
            State::Root => "Root",
            State::AtRule => "AtRule",
            State::Selector => "Selector",
            State::Property => "Property",
            State::PropertyValue => "PropertyValue",
            State::Comment => "Comment",
        };
    }
}