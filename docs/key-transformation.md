# Key Transformations

- [Overview](#1)
- [Generator Transformations](#2)
- [Applying Transformations](#3)

## Overview
Sometimes, it becomes necessary to modify the generated key to meet some demands - for example: uppercase key, splitted key with hyphens, etc. Transformations are convenient for this purpose. A **transformation** is simply a **[callable]** that can take a string as its first argument and returns a string. The Keygen package allows for one or more transformations to be applied on keys before they are generated.

## Generator Transformations

> **Transformations Queue**   

> Transformations can be registered on any generator instance. Every registered transformation on a generator is appended to the **transformations queue** of the generator. At the instantiation of a generator, the transformations queue is empty. It is possible to get the transformations queue of a generator by accessing the `$generator->transformations` property - which returns an `array` of callables in the order in which they have been registered.

The Keygen package provides two methods for registering transformations on a generator namely: `transformation()` and `transformations()`. Both of these methods require one or more **callables** or **array of callables** as arguments. Each callable represents a transformation. The callables are added to the transformations queue in the order in which they are specified. If any argument contains a non-callable, then an `InvalidTransformationKeygenException` is thrown.

There is a subtle difference in how these methods register transformations on a generator:

- `transformation()`   
    Appends the callables to the transformations queue in the order in which they are specified.

- `transformations()`   
    Clears the transformations queue and then appends the callables to a fresh transformations queue in the order in which they are specified. To clear the transformations queue without registering any new transformations, call the `transformations()` method with an empty array (`[]`) as its argument.

```php

```

## Applying Transformations
Immediately a key is generated, it is passed through all the callables registered in the transformations queue starting from the first to the last. The resulting key from each transformation is passed to the next transformation on the queue as its first argument.

When calling the `generate()` method, it is possible to specify as its argument(s), `callable` or `array` of callables that will be temporarily appended to the transformations queue of the generator for the key to be generated. Since, these transformations are temporarily appended to the queue, the original transformations queue remains unchanged. Applying transformations this way does not throw any exception if a non-callable is specified - it simply ignores it.

> **`generate()` Method**   

> If the first argument of the `generate()` is a `boolean`, it temporarily determines the affix inclusion behaviour of the generator for the key to be generated. See [Key Affixes] for more information about affix inclusion.

```php

```


[callable]: <http://php.net/manual/en/language.types.callable.php>
[Key Affixes]: <./key-affix.md>
