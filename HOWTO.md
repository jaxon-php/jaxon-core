Jaxon 3.x :: Quick start
------------------------

**Register a single class**

```php
jaxon()->register(Jaxon::CALLABLE_CLASS, 'HelloWorld');
```

**Register all classes in a directory**

```php
jaxon()->register(Jaxon::CALLABLE_DIR, '/full/path/to/dir');
```

**Register all classes in a namespace**

```php
jaxon()->register(Jaxon::CALLABLE_DIR, '/full/path/to/dir', [
    'namespace' => '\Path\To\Namespace',
]);
```

**Register a function**

```php
jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'sayHello');
```

**Register a method as a function**

```php
// The corresponding javascript function name is 'setColor'
jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'setColor', [
    'class' => 'HelloWorld',
]);
```

**Register a method as a function with an alias**

```php
// The corresponding javascript function name is 'helloWorld'
jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'sayHello', [
    'class' => 'HelloWorld',
    'alias' => 'helloWorld',
]);
```

**Call a registered class**

```php
<button onclick="<?php echo rq('HelloWorld')->call('sayHello') ?>" >Click Me</button>
```

**Call a registered class with a parameter**

```php
<button onclick="<?php echo rq('HelloWorld')->call('sayHello', 0) ?>" >Click Me</button>
<button onclick="<?php echo rq('HelloWorld')->call('setColor', pm()->select('color')) ?>" >Click Me</button>
```

**Call a registered function**

```php
<button onclick="<?php echo rq()->call('sayHello') ?>" >Click Me</button>
```
