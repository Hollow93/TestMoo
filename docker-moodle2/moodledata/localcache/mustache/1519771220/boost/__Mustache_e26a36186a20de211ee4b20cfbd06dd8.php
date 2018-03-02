<?php

class __Mustache_e26a36186a20de211ee4b20cfbd06dd8 extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';
        $blocksContext = array();

        $buffer .= $indent . '<nav class="list-group">
';
        // 'flatnavigation' section
        $value = $context->find('flatnavigation');
        $buffer .= $this->sectionFb0f52563d5cce9d95e4e779a3374cdf($context, $indent, $value);
        $buffer .= $indent . '</nav>
';

        return $buffer;
    }

    private function sectionE5c8e9bf60aae8648132c0b5393f93b1(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        $blocksContext = array();
    
        if (!is_string($value) && is_callable($value)) {
            $source = '
</nav>
<nav class="list-group m-t-1">
    ';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= $indent . '</nav>
';
                $buffer .= $indent . '<nav class="list-group m-t-1">
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function sectionE262a41669da4e6e3b97fcdc38c27010(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        $blocksContext = array();
    
        if (!is_string($value) && is_callable($value)) {
            $source = 'font-weight-bold';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= 'font-weight-bold';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function sectionDe9955ab2a0642065d5e708bf9e87436(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        $blocksContext = array();
    
        if (!is_string($value) && is_callable($value)) {
            $source = 'i/folder';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= 'i/folder';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function section98fe83782043c5e5bc6ead53a33b4301(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        $blocksContext = array();
    
        if (!is_string($value) && is_callable($value)) {
            $source = '
                <div class="media">
                    <span class="media-left">
                        {{#pix}}i/folder{{/pix}}
                    </span>
                    <span class="media-body">{{{text}}}</span>
                </div>
            ';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= $indent . '                <div class="media">
';
                $buffer .= $indent . '                    <span class="media-left">
';
                $buffer .= $indent . '                        ';
                // 'pix' section
                $value = $context->find('pix');
                $buffer .= $this->sectionDe9955ab2a0642065d5e708bf9e87436($context, $indent, $value);
                $buffer .= '
';
                $buffer .= $indent . '                    </span>
';
                $buffer .= $indent . '                    <span class="media-body">';
                $value = $this->resolveValue($context->find('text'), $context);
                $buffer .= $value;
                $buffer .= '</span>
';
                $buffer .= $indent . '                </div>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function section0ee4f1fbdc540d279cfe15d149ba97cd(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        $blocksContext = array();
    
        if (!is_string($value) && is_callable($value)) {
            $source = '
    <a class="list-group-item list-group-item-action {{#isactive}}font-weight-bold{{/isactive}}" href="{{{action}}}" data-key="{{key}}">
        <div class="m-l-{{get_indent}}">
            {{#is_section}}
                <div class="media">
                    <span class="media-left">
                        {{#pix}}i/folder{{/pix}}
                    </span>
                    <span class="media-body">{{{text}}}</span>
                </div>
            {{/is_section}}
            {{^is_section}}
                {{{text}}}
            {{/is_section}}
        </div>
    </a>
    ';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= $indent . '    <a class="list-group-item list-group-item-action ';
                // 'isactive' section
                $value = $context->find('isactive');
                $buffer .= $this->sectionE262a41669da4e6e3b97fcdc38c27010($context, $indent, $value);
                $buffer .= '" href="';
                $value = $this->resolveValue($context->find('action'), $context);
                $buffer .= $value;
                $buffer .= '" data-key="';
                $value = $this->resolveValue($context->find('key'), $context);
                $buffer .= call_user_func($this->mustache->getEscape(), $value);
                $buffer .= '">
';
                $buffer .= $indent . '        <div class="m-l-';
                $value = $this->resolveValue($context->find('get_indent'), $context);
                $buffer .= call_user_func($this->mustache->getEscape(), $value);
                $buffer .= '">
';
                // 'is_section' section
                $value = $context->find('is_section');
                $buffer .= $this->section98fe83782043c5e5bc6ead53a33b4301($context, $indent, $value);
                // 'is_section' inverted section
                $value = $context->find('is_section');
                if (empty($value)) {
                    
                    $buffer .= $indent . '                ';
                    $value = $this->resolveValue($context->find('text'), $context);
                    $buffer .= $value;
                    $buffer .= '
';
                }
                $buffer .= $indent . '        </div>
';
                $buffer .= $indent . '    </a>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function sectionD9d8417a223a3b51b5349f139be20ec2(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        $blocksContext = array();
    
        if (!is_string($value) && is_callable($value)) {
            $source = '
                {{#pix}}i/folder{{/pix}}
            ';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= $indent . '                ';
                // 'pix' section
                $value = $context->find('pix');
                $buffer .= $this->sectionDe9955ab2a0642065d5e708bf9e87436($context, $indent, $value);
                $buffer .= '
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function sectionFb0f52563d5cce9d95e4e779a3374cdf(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        $blocksContext = array();
    
        if (!is_string($value) && is_callable($value)) {
            $source = '
    {{#showdivider}}
</nav>
<nav class="list-group m-t-1">
    {{/showdivider}}
    {{#action}}
    <a class="list-group-item list-group-item-action {{#isactive}}font-weight-bold{{/isactive}}" href="{{{action}}}" data-key="{{key}}">
        <div class="m-l-{{get_indent}}">
            {{#is_section}}
                <div class="media">
                    <span class="media-left">
                        {{#pix}}i/folder{{/pix}}
                    </span>
                    <span class="media-body">{{{text}}}</span>
                </div>
            {{/is_section}}
            {{^is_section}}
                {{{text}}}
            {{/is_section}}
        </div>
    </a>
    {{/action}}
    {{^action}}
    <div class="list-group-item" data-key="{{key}}">
        <div class="m-l-{{get_indent}}">
            {{#is_section}}
                {{#pix}}i/folder{{/pix}}
            {{/is_section}}
            {{{text}}}
        </div>
    </div>
    {{/action}}
';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                // 'showdivider' section
                $value = $context->find('showdivider');
                $buffer .= $this->sectionE5c8e9bf60aae8648132c0b5393f93b1($context, $indent, $value);
                // 'action' section
                $value = $context->find('action');
                $buffer .= $this->section0ee4f1fbdc540d279cfe15d149ba97cd($context, $indent, $value);
                // 'action' inverted section
                $value = $context->find('action');
                if (empty($value)) {
                    
                    $buffer .= $indent . '    <div class="list-group-item" data-key="';
                    $value = $this->resolveValue($context->find('key'), $context);
                    $buffer .= call_user_func($this->mustache->getEscape(), $value);
                    $buffer .= '">
';
                    $buffer .= $indent . '        <div class="m-l-';
                    $value = $this->resolveValue($context->find('get_indent'), $context);
                    $buffer .= call_user_func($this->mustache->getEscape(), $value);
                    $buffer .= '">
';
                    // 'is_section' section
                    $value = $context->find('is_section');
                    $buffer .= $this->sectionD9d8417a223a3b51b5349f139be20ec2($context, $indent, $value);
                    $buffer .= $indent . '            ';
                    $value = $this->resolveValue($context->find('text'), $context);
                    $buffer .= $value;
                    $buffer .= '
';
                    $buffer .= $indent . '        </div>
';
                    $buffer .= $indent . '    </div>
';
                }
                $context->pop();
            }
        }
    
        return $buffer;
    }

}
