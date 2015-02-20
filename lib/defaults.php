<?php
    global $DEFAULT_OPTIONS, $DEF_FONT_SIZE, $DEF_LINE_HEIGHT;
    
    if (!isset($DEFAULT_OPTIONS))
    {
        define('DUMMY_STR', 'DUMMY');
        define('DEF_STAR_STYLE', 'oxygen');
        define('DEF_NERO_STYLE', 'thumbs');
        define('DEF_STAR_THEME', 'star_oxygen');
        define('DEF_NERO_THEME', 'thumbs_1');
        define('DEF_FREQUENCY', -1);
        
        $DEF_FONT_SIZE = new stdClass();
        $DEF_FONT_SIZE->TINY = '10px';
        $DEF_FONT_SIZE->SMALL = '12px';
        $DEF_FONT_SIZE->MEDIUM = '16px';
        $DEF_FONT_SIZE->LARGE = '20px';
        
        $DEF_LINE_HEIGHT = new stdClass();
        $DEF_LINE_HEIGHT->TINY = '12px';
        $DEF_LINE_HEIGHT->SMALL = '16px';
        $DEF_LINE_HEIGHT->MEDIUM = '20px';
        $DEF_LINE_HEIGHT->LARGE = '30px';

        $DEFAULT_OPTIONS = new stdClass();
        $DEFAULT_OPTIONS->boost = new stdClass();
        $DEFAULT_OPTIONS->imgUrl = new stdClass();
        $DEFAULT_OPTIONS->mobile = new stdClass();
        $DEFAULT_OPTIONS->label = new stdClass();
        $DEFAULT_OPTIONS->label->text = new stdClass();
        $DEFAULT_OPTIONS->label->text->star = new stdClass();
        $DEFAULT_OPTIONS->label->text->nero = new stdClass();
        $DEFAULT_OPTIONS->advanced = new stdClass();
        $DEFAULT_OPTIONS->advanced->star = new stdClass();
        $DEFAULT_OPTIONS->advanced->nero = new stdClass();
        $DEFAULT_OPTIONS->advanced->nero->text = new stdClass();
        $DEFAULT_OPTIONS->advanced->nero->text->like = new stdClass();
        $DEFAULT_OPTIONS->advanced->nero->text->dislike = new stdClass();
        $DEFAULT_OPTIONS->advanced->font = new stdClass();
        $DEFAULT_OPTIONS->advanced->font->hover = new stdClass();
        $DEFAULT_OPTIONS->advanced->text = new stdClass();
        $DEFAULT_OPTIONS->advanced->layout = new stdClass();
        $DEFAULT_OPTIONS->advanced->layout->align = new stdClass();
        $DEFAULT_OPTIONS->advanced->css = new stdClass();
        
        $DEFAULT_OPTIONS->uarid = 0;
        $DEFAULT_OPTIONS->lng = 'en';
        $DEFAULT_OPTIONS->url = '';
        $DEFAULT_OPTIONS->img = '';
        $DEFAULT_OPTIONS->title = '';
        $DEFAULT_OPTIONS->type = 'star';
        $DEFAULT_OPTIONS->rclass = '';
        $DEFAULT_OPTIONS->size = 'small';
        $DEFAULT_OPTIONS->theme = DEF_STAR_THEME;
        $DEFAULT_OPTIONS->style = 'oxygen';
        $DEFAULT_OPTIONS->imgUrl->ltr = '';
        $DEFAULT_OPTIONS->imgUrl->rtl = '';
        $DEFAULT_OPTIONS->mobile->optimized = true;
        $DEFAULT_OPTIONS->mobile->showTrigger = true;
        $DEFAULT_OPTIONS->label->background = '#FFFFFF';
        $DEFAULT_OPTIONS->label->text->star->empty = '{{text.rateThis}}';
        $DEFAULT_OPTIONS->label->text->star->normal = '{{text.rateThis}} ({{rating.votes}} {{text.votes}})';
        $DEFAULT_OPTIONS->label->text->star->rated = '{{rating.votes}} {{text.votes}}';
        $DEFAULT_OPTIONS->label->text->nero->empty = '{{text.rateThis}}';
        $DEFAULT_OPTIONS->label->text->nero->normal = '{{text.rateThis}}';
        $DEFAULT_OPTIONS->label->text->nero->rated = '{{rating.votes}} {{text.votes}}';
        $DEFAULT_OPTIONS->readOnly = false;
	    $DEFAULT_OPTIONS->sync = true;
	    $DEFAULT_OPTIONS->forceSync = false;
        $DEFAULT_OPTIONS->reVote = true;
        $DEFAULT_OPTIONS->frequency = DEF_FREQUENCY;
        $DEFAULT_OPTIONS->showInfo = true;
        $DEFAULT_OPTIONS->showTooltip = true;
        $DEFAULT_OPTIONS->showAverage = true;
        $DEFAULT_OPTIONS->showReport = true;
        $DEFAULT_OPTIONS->showRecommendations = false;
        $DEFAULT_OPTIONS->hideRecommendations = false;
        $DEFAULT_OPTIONS->showSponsored = false;
        $DEFAULT_OPTIONS->showLoader = true;
        $DEFAULT_OPTIONS->boost->votes = 0;
        $DEFAULT_OPTIONS->boost->rate = 5;
        $DEFAULT_OPTIONS->beforeRate = null;
        $DEFAULT_OPTIONS->afterRate = null;
        
        $DEFAULT_OPTIONS->advanced->star->stars = 5;

        $DEFAULT_OPTIONS->advanced->nero->showLike = true;
        $DEFAULT_OPTIONS->advanced->nero->showDislike = true;
        $DEFAULT_OPTIONS->advanced->nero->text->like->empty = '{{rating.likes}}';
        $DEFAULT_OPTIONS->advanced->nero->text->like->rated = '{{rating.likes}}';
        $DEFAULT_OPTIONS->advanced->nero->text->dislike->empty = '{{rating.dislikes}}';
        $DEFAULT_OPTIONS->advanced->nero->text->dislike->rated = '{{rating.dislikes}}';
        
        $DEFAULT_OPTIONS->advanced->font->bold = false;
        $DEFAULT_OPTIONS->advanced->font->italic = false;
        $DEFAULT_OPTIONS->advanced->font->color = '#000000';
        $DEFAULT_OPTIONS->advanced->font->size = $DEF_FONT_SIZE->SMALL;
        $DEFAULT_OPTIONS->advanced->font->type = 'inherit';
        
        $DEFAULT_OPTIONS->advanced->font->hover->color = '#000000';

        $DEFAULT_OPTIONS->advanced->layout->dir = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->layout->align->hor = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->layout->align->ver = 'middle';
        $DEFAULT_OPTIONS->advanced->layout->lineHeight = $DEF_LINE_HEIGHT->SMALL;
        
        $DEFAULT_OPTIONS->advanced->text->rateAwful = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->ratePoor = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->rateAverage = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->rateGood = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->rateExcellent = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->rateThis = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->like = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->dislike = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->vote = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->votes = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->thanks = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->outOf = DUMMY_STR;
        $DEFAULT_OPTIONS->advanced->text->weRecommend = DUMMY_STR;
        
        $DEFAULT_OPTIONS->advanced->css->container = '';
    }