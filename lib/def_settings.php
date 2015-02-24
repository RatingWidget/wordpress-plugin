<?php
    function rw_enrich_options1($settings, $defaults, $loadTheme = false, $fromDefaults = false)
    {
        $ret = @rw_get_default_value($settings, new stdClass());
        $ret->boost = @rw_get_default_value($settings->boost, new stdClass());
        $ret->imgUrl = @rw_get_default_value($settings->imgUrl, new stdClass());
        $ret->mobile = @rw_get_default_value($settings->mobile, new stdClass());
        $ret->label = @rw_get_default_value($settings->label, new stdClass());
        $ret->label->text = @rw_get_default_value($settings->label->text, new stdClass());
        $ret->label->text->star = @rw_get_default_value($settings->label->text->star, new stdClass());
        $ret->label->text->nero = @rw_get_default_value($settings->label->text->nero, new stdClass());
        $ret->advanced = @rw_get_default_value($settings->advanced, new stdClass());
        $ret->advanced->star = @rw_get_default_value($settings->advanced->star, new stdClass());
        $ret->advanced->nero = @rw_get_default_value($settings->advanced->nero, new stdClass());
        $ret->advanced->nero->text = @rw_get_default_value($settings->advanced->nero->text, new stdClass());
        $ret->advanced->nero->text->like = @rw_get_default_value($settings->advanced->nero->text->like, new stdClass());
        $ret->advanced->nero->text->dislike = @rw_get_default_value($settings->advanced->nero->text->dislike, new stdClass());
        $ret->advanced->font = @rw_get_default_value($settings->advanced->font, new stdClass());
        $ret->advanced->font->hover = @rw_get_default_value($settings->advanced->font->hover, new stdClass());
        $ret->advanced->layout = @rw_get_default_value($settings->advanced->layout, new stdClass());
        $ret->advanced->layout->align = @rw_get_default_value($settings->advanced->layout->align, new stdClass());
        $ret->advanced->text = @rw_get_default_value($settings->advanced->text, new stdClass());
        $ret->advanced->css = @rw_get_default_value($settings->advanced->css, new stdClass());
        
        if ($fromDefaults)
        {
            if (isset($settings->size))
            {
                require_once(dirname(__FILE__) . "/defaults.php");
                global $DEF_FONT_SIZE, $DEF_LINE_HEIGHT;
                $size = strtoupper($settings->size);
                $settings->advanced->font->size = @rw_get_default_value($settings->advanced->font->size, $DEF_FONT_SIZE->$size);
                $settings->advanced->layout->lineHeight = @rw_get_default_value($settings->advanced->layout->lineHeight, $DEF_LINE_HEIGHT->$size);
            }
            
            if (isset($settings->lng))
            {
                require(RW__PATH_LANGUAGES . $settings->lng . ".php");
                
                $settings->advanced->layout->dir = @rw_get_default_value($settings->advanced->layout->dir, $dir);
                $settings->advanced->layout->align->hor = @rw_get_default_value($settings->advanced->layout->align->hor, $hor);

                $settings->advanced->text->rateAwful = @rw_get_default_value($settings->advanced->text->rateAwful, $dictionary["rateAwful"]);
                $settings->advanced->text->ratePoor = @rw_get_default_value($settings->advanced->text->ratePoor, $dictionary["ratePoor"]);
                $settings->advanced->text->rateAverage = @rw_get_default_value($settings->advanced->text->rateAverage, $dictionary["rateAverage"]);
                $settings->advanced->text->rateGood = @rw_get_default_value($settings->advanced->text->rateGood, $dictionary["rateGood"]);
                $settings->advanced->text->rateExcellent = @rw_get_default_value($settings->advanced->text->rateExcellent, $dictionary["rateExcellent"]);
                $settings->advanced->text->rateThis = @rw_get_default_value($settings->advanced->text->rateThis, $dictionary["rateThis"]);
                $settings->advanced->text->like = @rw_get_default_value($settings->advanced->text->like, $dictionary["like"]);
                $settings->advanced->text->dislike = @rw_get_default_value($settings->advanced->text->dislike, $dictionary["dislike"]);
                $settings->advanced->text->vote = @rw_get_default_value($settings->advanced->text->vote, $dictionary["vote"]);
                $settings->advanced->text->votes = @rw_get_default_value($settings->advanced->text->votes, $dictionary["votes"]);
                $settings->advanced->text->thanks = @rw_get_default_value($settings->advanced->text->thanks, $dictionary["thanks"]);
                $settings->advanced->text->outOf = @rw_get_default_value($settings->advanced->text->outOf, $dictionary["outOf"]);
                $settings->advanced->text->weRecommend = @rw_get_default_value($settings->advanced->text->weRecommend, $dictionary["weRecommend"]);
            }
        }
        
        $hasTheme = $loadTheme && isset($settings->theme);

        require_once(RW__PATH_THEMES . "dir.php");
        
        global $RW_THEMES;
        
        // Get rating type.
        $ret->type = @rw_get_default_value($settings->type, (!$hasTheme ? $defaults->type : ($RW_THEMES['star'][$settings->theme] ? 'star' : 'nero')));

        if ($hasTheme)
        {
            // Load theme options.
            require(RW__PATH_THEMES . $RW_THEMES[$ret->type][$settings->theme]["file"]);
            
            return rw_enrich_options1($settings, rw_enrich_options1($theme_options, $defaults));
        }
        
        $ret->uarid = @rw_get_default_value($settings->uarid, $defaults->uarid);
        $ret->lng = @rw_get_default_value($settings->lng, $defaults->lng);
        $ret->url = @rw_get_default_value($settings->url, $defaults->url);
        $ret->img = @rw_get_default_value($settings->img, $defaults->img);
        $ret->title = @rw_get_default_value($settings->title, $defaults->title);
        $ret->rclass = @rw_get_default_value($settings->rclass, $defaults->rclass);
        $ret->size = @rw_get_default_value($settings->size, $defaults->size);
        $ret->style = @rw_get_default_value($settings->style, $defaults->style);
        $ret->imgUrl->ltr = @rw_get_default_value($settings->imgUrl->ltr, $defaults->imgUrl->ltr);
        $ret->imgUrl->rtl = @rw_get_default_value($settings->imgUrl->rtl, $defaults->imgUrl->rtl);
        $ret->mobile->optimized = @rw_get_default_value($settings->mobile->optimized, $defaults->mobile->optimized);
        $ret->mobile->showTrigger = @rw_get_default_value($settings->mobile->showTrigger, $defaults->mobile->showTrigger);
        $ret->label->background = @rw_get_default_value($settings->label->background, $defaults->label->background);
        $ret->label->text->star->empty = @rw_get_default_value($settings->label->text->star->empty, $defaults->label->text->star->empty);
        $ret->label->text->star->normal = @rw_get_default_value($settings->label->text->star->normal, $defaults->label->text->star->normal);
        $ret->label->text->star->rated = @rw_get_default_value($settings->label->text->star->rated, $defaults->label->text->star->rated);
        $ret->label->text->nero->empty = @rw_get_default_value($settings->label->text->nero->empty, $defaults->label->text->nero->empty);
        $ret->label->text->nero->normal = @rw_get_default_value($settings->label->text->nero->normal, $defaults->label->text->nero->normal);
        $ret->label->text->nero->rated = @rw_get_default_value($settings->label->text->nero->rated, $defaults->label->text->nero->rated);
        $ret->readOnly = @rw_get_default_value($settings->readOnly, $defaults->readOnly);
	    $ret->sync = @rw_get_default_value($settings->sync, $defaults->sync);
	    $ret->forceSync = @rw_get_default_value($settings->forceSync, $defaults->forceSync);
        $ret->reVote = @rw_get_default_value($settings->reVote, $defaults->reVote);
        $ret->frequency = @rw_get_default_value($settings->frequency, $defaults->frequency);
        $ret->showInfo = @rw_get_default_value($settings->showInfo, $defaults->showInfo);
        $ret->showTooltip = @rw_get_default_value($settings->showTooltip, $defaults->showTooltip);
        $ret->showAverage = @rw_get_default_value($settings->showAverage, $defaults->showAverage);
        $ret->showReport = @rw_get_default_value($settings->showReport, $defaults->showReport);
        $ret->showRecommendations = @rw_get_default_value($settings->showRecommendations, $defaults->showRecommendations);
        $ret->hideRecommendations = @rw_get_default_value($settings->hideRecommendations, $defaults->hideRecommendations);
        $ret->showSponsored = @rw_get_default_value($settings->showSponsored, $defaults->showSponsored);
        $ret->showLoader = @rw_get_default_value($settings->showLoader, $defaults->showLoader);
        $ret->beforeRate = @rw_get_default_value($settings->beforeRate, $defaults->beforeRate);
        $ret->afterRate = @rw_get_default_value($settings->afterRate, $defaults->afterRate);
        
        $ret->boost->votes = @rw_get_default_value($settings->boost->votes, $defaults->boost->votes);
        $ret->boost->rate = @rw_get_default_value($settings->boost->rate, $defaults->boost->rate);

        $ret->advanced->star->stars = @rw_get_default_value($settings->advanced->star->stars, $defaults->advanced->star->stars);

        $ret->advanced->nero->showDislike = @rw_get_default_value($settings->advanced->nero->showDislike, $defaults->advanced->nero->showDislike);
        $ret->advanced->nero->showLike = @rw_get_default_value($settings->advanced->nero->showLike, $defaults->advanced->nero->showLike);
        $ret->advanced->nero->text->like->empty = @rw_get_default_value($settings->advanced->nero->text->like->empty, $defaults->advanced->nero->text->like->empty);
        $ret->advanced->nero->text->like->rated = @rw_get_default_value($settings->advanced->nero->text->like->rated, $defaults->advanced->nero->text->like->rated);
        $ret->advanced->nero->text->dislike->empty = @rw_get_default_value($settings->advanced->nero->text->dislike->empty, $defaults->advanced->nero->text->dislike->empty);
        $ret->advanced->nero->text->dislike->rated = @rw_get_default_value($settings->advanced->nero->text->dislike->rated, $defaults->advanced->nero->text->dislike->rated);

        $ret->advanced->font->bold = @rw_get_default_value($settings->advanced->font->bold, $defaults->advanced->font->bold);
        $ret->advanced->font->italic = @rw_get_default_value($settings->advanced->font->italic, $defaults->advanced->font->italic);
        $ret->advanced->font->color = @rw_get_default_value($settings->advanced->font->color, $defaults->advanced->font->color);
        $ret->advanced->font->size = @rw_get_default_value($settings->advanced->font->size, $defaults->advanced->font->size);
        $ret->advanced->font->type = @rw_get_default_value($settings->advanced->font->type, $defaults->advanced->font->type);
        $ret->advanced->font->hover->color = @rw_get_default_value($settings->advanced->font->hover->color, $defaults->advanced->font->hover->color);

        $ret->advanced->layout->dir = @rw_get_default_value($settings->advanced->layout->dir, $defaults->advanced->layout->dir);
        $ret->advanced->layout->lineHeight = @rw_get_default_value($settings->advanced->layout->lineHeight, $defaults->advanced->layout->lineHeight);
        $ret->advanced->layout->align->hor = @rw_get_default_value($settings->advanced->layout->align->hor, $defaults->advanced->layout->align->hor);
        $ret->advanced->layout->align->ver = @rw_get_default_value($settings->advanced->layout->align->ver, $defaults->advanced->layout->align->ver);

        $ret->advanced->text->rateAwful = @rw_get_default_value($settings->advanced->text->rateAwful, $defaults->advanced->text->rateAwful);
        $ret->advanced->text->ratePoor = @rw_get_default_value($settings->advanced->text->ratePoor, $defaults->advanced->text->ratePoor);
        $ret->advanced->text->rateAverage = @rw_get_default_value($settings->advanced->text->rateAverage, $defaults->advanced->text->rateAverage);
        $ret->advanced->text->rateGood = @rw_get_default_value($settings->advanced->text->rateGood, $defaults->advanced->text->rateGood);
        $ret->advanced->text->rateExcellent = @rw_get_default_value($settings->advanced->text->rateExcellent, $defaults->advanced->text->rateExcellent);
        $ret->advanced->text->rateThis = @rw_get_default_value($settings->advanced->text->rateThis, $defaults->advanced->text->rateThis);
        $ret->advanced->text->like = @rw_get_default_value($settings->advanced->text->like, $defaults->advanced->text->like);
        $ret->advanced->text->dislike = @rw_get_default_value($settings->advanced->text->dislike, $defaults->advanced->text->dislike);
        $ret->advanced->text->vote = @rw_get_default_value($settings->advanced->text->vote, $defaults->advanced->text->vote);
        $ret->advanced->text->votes = @rw_get_default_value($settings->advanced->text->votes, $defaults->advanced->text->votes);
        $ret->advanced->text->thanks = @rw_get_default_value($settings->advanced->text->thanks, $defaults->advanced->text->thanks);
        $ret->advanced->text->outOf = @rw_get_default_value($settings->advanced->text->outOf, $defaults->advanced->text->outOf);
        $ret->advanced->text->weRecommend = @rw_get_default_value($settings->advanced->text->weRecommend, $defaults->advanced->text->weRecommend);
        
        $ret->advanced->css->container = @rw_get_default_value($settings->advanced->css->container, $defaults->advanced->css->container);
        
        return $ret;
    }
    
    function rw_set_language_options(&$settings, $dictionary = array(), $dir = "ltr", $hor = "right")
    {
        $settings = @rw_get_default_value($settings, new stdClass());
        $settings->advanced = @rw_get_default_value($settings->advanced, new stdClass());
        $settings->advanced->text = @rw_get_default_value($settings->advanced->text, new stdClass());
        $settings->advanced->layout = @rw_get_default_value($settings->advanced->layout, new stdClass());
        $settings->advanced->layout->align = @rw_get_default_value($settings->advanced->layout->align, new stdClass());

        $settings->advanced->layout->dir = @rw_get_default_value($settings->advanced->layout->dir, $dir, DUMMY_STR);
        $settings->advanced->layout->align->hor = @rw_get_default_value($settings->advanced->layout->align->hor, $hor, DUMMY_STR);

        $settings->advanced->text->rateAwful = @rw_get_default_value($settings->advanced->text->rateAwful, $dictionary["rateAwful"], DUMMY_STR);
        $settings->advanced->text->ratePoor = @rw_get_default_value($settings->advanced->text->ratePoor, $dictionary["ratePoor"], DUMMY_STR);
        $settings->advanced->text->rateAverage = @rw_get_default_value($settings->advanced->text->rateAverage, $dictionary["rateAverage"], DUMMY_STR);
        $settings->advanced->text->rateGood = @rw_get_default_value($settings->advanced->text->rateGood, $dictionary["rateGood"], DUMMY_STR);
        $settings->advanced->text->rateExcellent = @rw_get_default_value($settings->advanced->text->rateExcellent, $dictionary["rateExcellent"], DUMMY_STR);
        $settings->advanced->text->rateThis = @rw_get_default_value($settings->advanced->text->rateThis, $dictionary["rateThis"], DUMMY_STR);
        $settings->advanced->text->like = @rw_get_default_value($settings->advanced->text->like, $dictionary["like"], DUMMY_STR);
        $settings->advanced->text->dislike = @rw_get_default_value($settings->advanced->text->dislike, $dictionary["dislike"], DUMMY_STR);
        $settings->advanced->text->vote = @rw_get_default_value($settings->advanced->text->vote, $dictionary["vote"], DUMMY_STR);
        $settings->advanced->text->votes = @rw_get_default_value($settings->advanced->text->votes, $dictionary["votes"], DUMMY_STR);
        $settings->advanced->text->thanks = @rw_get_default_value($settings->advanced->text->thanks, $dictionary["thanks"], DUMMY_STR);
        $settings->advanced->text->outOf = @rw_get_default_value($settings->advanced->text->outOf, $dictionary["outOf"], DUMMY_STR);
        $settings->advanced->text->weRecommend = @rw_get_default_value($settings->advanced->text->weRecommend, $dictionary["weRecommend"], DUMMY_STR);
    }
    
    function rw_enrich_options(&$settings, $dictionary = array(), $dir = "ltr", $hor = "right", $type = "star")
    {
        $settings = @rw_get_default_value($settings, new stdClass());
        $settings->boost = @rw_get_default_value($settings->boost, new stdClass());
        $settings->label = @rw_get_default_value($settings->label, new stdClass());
        $settings->label->text = @rw_get_default_value($settings->label->text, new stdClass());
        $settings->label->text->star = @rw_get_default_value($settings->label->text->star, new stdClass());
        $settings->label->text->nero = @rw_get_default_value($settings->label->text->nero, new stdClass());
        $settings->advanced = @rw_get_default_value($settings->advanced, new stdClass());
        $settings->advanced->font = @rw_get_default_value($settings->advanced->font, new stdClass());
        $settings->advanced->font->hover = @rw_get_default_value($settings->advanced->font->hover, new stdClass());
        $settings->advanced->layout = @rw_get_default_value($settings->advanced->layout, new stdClass());
        $settings->advanced->layout->align = @rw_get_default_value($settings->advanced->layout->align, new stdClass());
        $settings->advanced->text = @rw_get_default_value($settings->advanced->text, new stdClass());
        $settings->advanced->star = @rw_get_default_value($settings->advanced->star, new stdClass());
        $settings->advanced->nero = @rw_get_default_value($settings->advanced->nero, new stdClass());
        $settings->advanced->nero->text = @rw_get_default_value($settings->advanced->nero->text, new stdClass());
        $settings->advanced->nero->text->like = @rw_get_default_value($settings->advanced->nero->text->like, new stdClass());
        $settings->advanced->nero->text->dislike = @rw_get_default_value($settings->advanced->nero->text->dislike, new stdClass());
        
        $settings->lng = @rw_get_default_value($settings->lng, "en");
        $settings->url = @rw_get_default_value($settings->url, '');
        $settings->img = @rw_get_default_value($settings->img, '');
        $settings->title = @rw_get_default_value($settings->title, '');
        $settings->type = @rw_get_default_value($settings->type, $type);
        $settings->rclass = @rw_get_default_value($settings->rclass, "");
        $settings->size = @rw_get_default_value($settings->size, "small");
        $settings->style = @rw_get_default_value($settings->style, "oxygen");
        $settings->imgUrl->ltr = @rw_get_default_value($settings->imgUrl->ltr, "");
        $settings->imgUrl->rtl = @rw_get_default_value($settings->imgUrl->rtl, "");
        $settings->mobile->optimized = @rw_get_default_value($settings->mobile->optimized, true);
        $settings->mobile->showTrigger = @rw_get_default_value($settings->mobile->showTrigger, true);
        $settings->readOnly = @rw_get_default_value($settings->readOnly, false);
	    $settings->sync = @rw_get_default_value($settings->sync, true);
	    $settings->forceSync = @rw_get_default_value($settings->forceSync, false);
        $settings->frequency = @rw_get_default_value($settings->frequency, DEF_FREQUENCY);
        $settings->showInfo = @rw_get_default_value($settings->showInfo, true);
        $settings->showTooltip = @rw_get_default_value($settings->showTooltip, true);
        $settings->showAverage = @rw_get_default_value($settings->showAverage, true);
        $settings->showReport = @rw_get_default_value($settings->showReport, true);
        $settings->showRecommendations = @rw_get_default_value($settings->showRecommendations, false);
        $settings->hideRecommendations = @rw_get_default_value($settings->hideRecommendations, false);
        $settings->showSponsored = @rw_get_default_value($settings->showSponsored, false);
        $settings->showLoader = @rw_get_default_value($settings->showLoader, true);
        $settings->beforeRate = @rw_get_default_value($settings->beforeRate, null);
        $settings->afterRate = @rw_get_default_value($settings->beforeRate, null);
        
        $settings->boost->votes = @rw_get_default_value($settings->boost->votes, 0);
        $settings->boost->rate = @rw_get_default_value($settings->boost->rate, 5);

        $settings->label->background = @rw_get_default_value($settings->label->background, '#FFFFFF');
        $settings->label->text->star->empty = @rw_get_default_value($settings->label->text->star->empty, '{{text.rateThis}}');
        $settings->label->text->star->normal = @rw_get_default_value($settings->label->text->star->normal, '{{text.rateThis}} ({{rating.votes}} {{text.votes}})');
        $settings->label->text->star->rated = @rw_get_default_value($settings->label->text->star->rated, '{{rating.votes}} {{text.votes}}');
        $settings->label->text->nero->empty = @rw_get_default_value($settings->label->text->nero->empty, '{{text.rateThis}}');
        $settings->label->text->nero->normal = @rw_get_default_value($settings->label->text->nero->normal, '{{text.rateThis}}');
        $settings->label->text->nero->rated = @rw_get_default_value($settings->label->text->nero->rated, '{{rating.votes}} {{text.votes}}');

        $settings->advanced->star->stars = @rw_get_default_value($settings->advanced->star->stars, 5);
        $settings->advanced->nero->showLike = @rw_get_default_value($settings->advanced->nero->showLike, true);
        $settings->advanced->nero->showDislike = @rw_get_default_value($settings->advanced->nero->showDislike, true);
        $settings->advanced->nero->text->like->empty = @rw_get_default_value($settings->advanced->nero->text->like->empty, '{{rating.likes}}');
        $settings->advanced->nero->text->like->rated = @rw_get_default_value($settings->advanced->nero->text->like->rated, '{{rating.likes}}');
        $settings->advanced->nero->text->dislike->empty = @rw_get_default_value($settings->advanced->nero->text->dislike->empty, '{{rating.dislikes}}');
        $settings->advanced->nero->text->dislike->rated = @rw_get_default_value($settings->advanced->nero->text->dislike->rated, '{{rating.dislikes}}');
        
        $settings->advanced->font->bold = @rw_get_default_value($settings->advanced->font->bold, false);
        $settings->advanced->font->italic = @rw_get_default_value($settings->advanced->font->italic, false);
        $settings->advanced->font->color = @rw_get_default_value($settings->advanced->font->color, "#000000");
        $settings->advanced->font->size = @rw_get_default_value($settings->advanced->font->size, "12px");
        $settings->advanced->font->type = @rw_get_default_value($settings->advanced->font->type, "inherit");
        $settings->advanced->font->hover->color = @rw_get_default_value($settings->advanced->font->hover->color, "#000000");

        $settings->advanced->layout->dir = @rw_get_default_value($settings->advanced->layout->dir, $dir);
        $settings->advanced->layout->lineHeight = @rw_get_default_value($settings->advanced->layout->lineHeight, "18px");
        $settings->advanced->layout->align->hor = @rw_get_default_value($settings->advanced->layout->align->hor, $hor);
        $settings->advanced->layout->align->ver = @rw_get_default_value($settings->advanced->layout->align->ver, "middle");

        $settings->advanced->text->rateAwful = @rw_get_default_value($settings->advanced->text->rateAwful, $dictionary["rateAwful"]);
        $settings->advanced->text->ratePoor = @rw_get_default_value($settings->advanced->text->ratePoor, $dictionary["ratePoor"]);
        $settings->advanced->text->rateAverage = @rw_get_default_value($settings->advanced->text->rateAverage, $dictionary["rateAverage"]);
        $settings->advanced->text->rateGood = @rw_get_default_value($settings->advanced->text->rateGood, $dictionary["rateGood"]);
        $settings->advanced->text->rateExcellent = @rw_get_default_value($settings->advanced->text->rateExcellent, $dictionary["rateExcellent"]);
        $settings->advanced->text->rateThis = @rw_get_default_value($settings->advanced->text->rateThis, $dictionary["rateThis"]);
        $settings->advanced->text->like = @rw_get_default_value($settings->advanced->text->like, $dictionary["like"]);
        $settings->advanced->text->dislike = @rw_get_default_value($settings->advanced->text->dislike, $dictionary["dislike"]);
        $settings->advanced->text->vote = @rw_get_default_value($settings->advanced->text->vote, $dictionary["vote"]);
        $settings->advanced->text->votes = @rw_get_default_value($settings->advanced->text->votes, $dictionary["votes"]);
        $settings->advanced->text->thanks = @rw_get_default_value($settings->advanced->text->thanks, $dictionary["thanks"]);
        $settings->advanced->text->outOf = @rw_get_default_value($settings->advanced->text->outOf, $dictionary["outOf"]);
        $settings->advanced->text->weRecommend = @rw_get_default_value($settings->advanced->text->weRecommend, $dictionary["weRecommend"]);
    }
        
    function rw_get_default_value($val, $def, $null = null)
    {
        return ((isset($val) && $val !== $null) ? $val : $def);
    }

    function rw_get_default_obj_value(&$objects, &$address, $defVal, $null = null)
    {
        foreach ($objects as $obj)
        {
            if (!isset($obj))
                continue;
            
            $hasValue = true;
            
            $cur = $obj;
            
            foreach ($address as $p)
            {
                if (!isset($cur->$p))
                {
                    $hasValue = false;
                    break;
                }
                
                $cur = $cur->$p;
            }
            
            if ($hasValue)
                return $cur;
        }
        
        return $defVal;
    }