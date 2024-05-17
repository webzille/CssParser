<?php

namespace Webzille\CssParser\Enum;

enum State
{
    case Root;
    case AtRule;
    case Selector;
    case Property;
    case PropertyValue;
    case Comment;

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