/*
Tagger - Converts any text input box into an easy-to-edit multiple-tag interface.
*/

(function()
{
    var trim = function(str)
    {
        return str.replace(/^\s+|\s+$/g, '');
    };
    
    var Tagger = function(element, options)
    {
        var obj = this;
        var element = $(element);
        var container = new Element('div');
        var input = new Element('input');
        
        var config = {
            'class':        'tagger',
            inputAutosize:  true,
            beforeAdd:      Prototype.emptyFunction,
            afterAdd:       Prototype.emptyFunction,
            beforeEdit:     Prototype.emptyFunction,
            afterEdit:      Prototype.emptyFunction,
            beforeDelete:   Prototype.emptyFunction,
            afterDelete:    Prototype.emptyFunction
        };
        
        Object.extend(config, options || {});
        
        this.getContainer = function()
        {
            return container;
        };
        
        this.getConfig = function()
        {
            return config;
        };
        
        this.addTag = function(tag)
        {
            if (false === config.beforeAdd(tag, obj)) return obj;
            
            if (tag.getContainer !== undefined) {
                input.insert({before: tag.getContainer()});
            }
            
            obj.updateRealInput();
            config.afterAdd(tag, obj);
            
            return obj;
        };
        
        if (config['class']) container.addClassName(config['class']);
        element.hide();
        element.insert({after: container});
        container.insert(input);
        
        container.observe('click', function()
        {
            input.focus();
        });
        
        this.insertCurrentInput = function()
        {
            var val = input.getValue();
            
            if (val) {
                var tag = new Tag(obj);
                tag.setTag(val);
                obj.addTag(tag);
            }
            
            input.clear();
        };
        
        input.observe('keydown', function(event)
        {
            switch (event.keyCode) {
                // comma, enter
                case 188:
                case 13:
                    obj.insertCurrentInput();
                    event.stop();
                    break;
            }
        });
        
        input.observe('keypress', function(event)
        {
            // Detect backspace
            if (event.keyCode != 8) return;
            
            if (!input.getValue()) {
                // "click" last item
                var lastTag = container.select('span.tag:last-of-type').invoke('fire', 'tag:click');
                event.stop();
            }
        });
        
        input.observe('blur', obj.insertCurrentInput);
        
        this.autogrow = function()
        {
            if (true != config.inputAutosize) return;
            var size = input.getValue().length + 1;
            input.writeAttribute('size', size);
        };
        
        this.getInput = function()
        {
            return input;
        };
        
        this.createTag = function(tagName)
        {
            var tag = new Tag(obj);
            tag.setTag(tagName);
            obj.addTag(tag);
        };
        
        this.updateRealInput = function()
        {
            var allTags = container.select('span.tag').pluck('innerHTML');
            var tags = allTags.join(', ');
            if (allTags == null)
            	tags = '';
            element.value = tags;
            //element.writeAttribute('value', tags);
        };
        
        input.observe('change', this.autogrow);
        input.observe('keyup', this.autogrow);
        
        // Handle current value
        var tagString = element.getValue();
        if (tagString) {
            var tags = tagString.split(',');
            tags.each(function(tagName)
            {
                obj.createTag(tagName);
            });
        }
    };
    
    var Tag = function(tagger)
    {
        var obj = this;
        var tagger = tagger;
        var container = new Element('span');
        container.addClassName('tag');
        
        this.getContainer = function()
        {
            return container;
        };
        
        this.remove = function()
        {
        	tagger.updateRealInput();
            return container.remove();
        };
        
        this.setTag = function(newTag)
        {
            tag = trim(newTag);
            if (!tag) {
                if (false === tagger.getConfig().beforeDelete(obj, tagger)) return;
                
                var previous = container.previous('span.tag');
                if (previous) {
                    previous.fire('tag:click');
                } else {
                    tagger.getInput().show().focus();
                }
                container.remove();
                tagger.getConfig().afterDelete(obj, tagger);
                
                return;
            }
            tagger.getInput().show().focus();
            container.innerHTML = tag;
            
            tagger.updateRealInput();
        };
        
        this.edit = function(event)
        {
            if (false === tagger.getConfig().beforeEdit(obj, tagger)) return;
            
            tagger.getInput().hide();
            var input = new Element('input', {type: 'text'});
            
            var doneEditing = function()
            {
                input.remove();
                obj.setTag(input.getValue());
                container.removeClassName('editing');
                tagger.getConfig().afterEdit(obj, tagger);
                tagger.updateRealInput();
            };
            
            input.setStyle({width: container.getWidth() + 'px'});
            input.value = container.innerHTML;
            input.observe('blur', doneEditing);
            input.observe('keydown', function(event)
            {
                switch (event.keyCode) {
                    // comma, enter
                    case 188:
                    case 13:
                        doneEditing();
                        event.stop();
                        break;
                }
            });
            
            input.observe('keypress', function(event)
            {
                // Backspace
                if (event.keyCode != 8) return;
                
                if (!input.getValue()) {
                    doneEditing();
                    event.stop();
                }
            });
            
            input.observe('click', function(event)
            {
                event.stop();
            });
            
            container.update(input);
            container.addClassName('editing');
            input.focus();
            
            event.stop();
        };
        
        container.observe('click', this.edit);
        container.observe('tag:click', this.edit);
    };
    
    Tagger.Tag = Tag;
    
    if (!window.Virgen) window.Virgen = {};
    window.Virgen.Tagger = Tagger;
})();