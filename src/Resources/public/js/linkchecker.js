(function (root) {

    // Store setTimeout reference so promise-polyfill will be unaffected by
    // other code modifying setTimeout (like sinon.useFakeTimers())
    var setTimeoutFunc = setTimeout;

    function noop() {}

    // Polyfill for Function.prototype.bind
    function bind(fn, thisArg) {
        return function () {
            fn.apply(thisArg, arguments);
        };
    }

    function Promise(fn) {
        if (typeof this !== 'object') throw new TypeError('Promises must be constructed via new');
        if (typeof fn !== 'function') throw new TypeError('not a function');
        this._state = 0;
        this._handled = false;
        this._value = undefined;
        this._deferreds = [];

        doResolve(fn, this);
    }

    function handle(self, deferred) {
        while (self._state === 3) {
            self = self._value;
        }
        if (self._state === 0) {
            self._deferreds.push(deferred);
            return;
        }
        self._handled = true;
        Promise._immediateFn(function () {
            var cb = self._state === 1 ? deferred.onFulfilled : deferred.onRejected;
            if (cb === null) {
                (self._state === 1 ? resolve : reject)(deferred.promise, self._value);
                return;
            }
            var ret;
            try {
                ret = cb(self._value);
            } catch (e) {
                reject(deferred.promise, e);
                return;
            }
            resolve(deferred.promise, ret);
        });
    }

    function resolve(self, newValue) {
        try {
            // Promise Resolution Procedure: https://github.com/promises-aplus/promises-spec#the-promise-resolution-procedure
            if (newValue === self) throw new TypeError('A promise cannot be resolved with itself.');
            if (newValue && (typeof newValue === 'object' || typeof newValue === 'function')) {
                var then = newValue.then;
                if (newValue instanceof Promise) {
                    self._state = 3;
                    self._value = newValue;
                    finale(self);
                    return;
                } else if (typeof then === 'function') {
                    doResolve(bind(then, newValue), self);
                    return;
                }
            }
            self._state = 1;
            self._value = newValue;
            finale(self);
        } catch (e) {
            reject(self, e);
        }
    }

    function reject(self, newValue) {
        self._state = 2;
        self._value = newValue;
        finale(self);
    }

    function finale(self) {
        if (self._state === 2 && self._deferreds.length === 0) {
            Promise._immediateFn(function() {
                if (!self._handled) {
                    Promise._unhandledRejectionFn(self._value);
                }
            });
        }

        for (var i = 0, len = self._deferreds.length; i < len; i++) {
            handle(self, self._deferreds[i]);
        }
        self._deferreds = null;
    }

    function Handler(onFulfilled, onRejected, promise) {
        this.onFulfilled = typeof onFulfilled === 'function' ? onFulfilled : null;
        this.onRejected = typeof onRejected === 'function' ? onRejected : null;
        this.promise = promise;
    }

    /**
     * Take a potentially misbehaving resolver function and make sure
     * onFulfilled and onRejected are only called once.
     *
     * Makes no guarantees about asynchrony.
     */
    function doResolve(fn, self) {
        var done = false;
        try {
            fn(function (value) {
                if (done) return;
                done = true;
                resolve(self, value);
            }, function (reason) {
                if (done) return;
                done = true;
                reject(self, reason);
            });
        } catch (ex) {
            if (done) return;
            done = true;
            reject(self, ex);
        }
    }

    Promise.prototype['catch'] = function (onRejected) {
        return this.then(null, onRejected);
    };

    Promise.prototype.then = function (onFulfilled, onRejected) {
        var prom = new (this.constructor)(noop);

        handle(this, new Handler(onFulfilled, onRejected, prom));
        return prom;
    };

    Promise.all = function (arr) {
        var args = Array.prototype.slice.call(arr);

        return new Promise(function (resolve, reject) {
            if (args.length === 0) return resolve([]);
            var remaining = args.length;

            function res(i, val) {
                try {
                    if (val && (typeof val === 'object' || typeof val === 'function')) {
                        var then = val.then;
                        if (typeof then === 'function') {
                            then.call(val, function (val) {
                                res(i, val);
                            }, reject);
                            return;
                        }
                    }
                    args[i] = val;
                    if (--remaining === 0) {
                        resolve(args);
                    }
                } catch (ex) {
                    reject(ex);
                }
            }

            for (var i = 0; i < args.length; i++) {
                res(i, args[i]);
            }
        });
    };

    Promise.resolve = function (value) {
        if (value && typeof value === 'object' && value.constructor === Promise) {
            return value;
        }

        return new Promise(function (resolve) {
            resolve(value);
        });
    };

    Promise.reject = function (value) {
        return new Promise(function (resolve, reject) {
            reject(value);
        });
    };

    Promise.race = function (values) {
        return new Promise(function (resolve, reject) {
            for (var i = 0, len = values.length; i < len; i++) {
                values[i].then(resolve, reject);
            }
        });
    };

    // Use polyfill for setImmediate for performance gains
    Promise._immediateFn = (typeof setImmediate === 'function' && function (fn) { setImmediate(fn); }) ||
        function (fn) {
            setTimeoutFunc(fn, 0);
        };

    Promise._unhandledRejectionFn = function _unhandledRejectionFn(err) {
        if (typeof console !== 'undefined' && console) {
            console.warn('Possible Unhandled Promise Rejection:', err); // eslint-disable-line no-console
        }
    };

    /**
     * Set the immediate function to execute callbacks
     * @param fn {function} Function to execute
     * @deprecated
     */
    Promise._setImmediateFn = function _setImmediateFn(fn) {
        Promise._immediateFn = fn;
    };

    /**
     * Change the function to execute on unhandled rejection
     * @param {function} fn Function to execute on unhandled rejection
     * @deprecated
     */
    Promise._setUnhandledRejectionFn = function _setUnhandledRejectionFn(fn) {
        Promise._unhandledRejectionFn = fn;
    };

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = Promise;
    } else if (!root.Promise) {
        root.Promise = Promise;
    }

})(this);


(function () {
    var LinkChecker, _requests = [];

    function LinkChecker(element, options) {
        this.element = element;
        this.options = options;
        this.url = this.element.getAttribute('data-url');
        this.element.linkchecker = this;

        if (this.element.getAttribute('data-target')) {
            this.target = document.querySelector(this.element.getAttribute('data-target'));
        }

        if (this.target == 'undefined') {
            return false;
        }


        if (!this.url) {
            this.url = window.location.href;
        }


        var params = {};

        this.url.replace(
            /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
            function (m, key, value) { // callback
                params[key] = value !== undefined ? value : '';
            }
        );

        this.params = params;
    }

    LinkChecker.prototype.test = function () {
        return (function (_this) {
            return new Promise(function (resolve, reject) {
                method = "post";
                url = _this.element.getAttribute('data-url');

                var xhr = new XMLHttpRequest();
                xhr.open(method, url, true);
                xhr.withCredentials = !!_this.options.withCredentials;
                response = null;
                updateProgress = function() {
                    return function (e) {
                    };
                };
                xhr.onload = function(){
                    var _ref;
                    if (xhr.readyState !== 4) {
                        reject({
                            status: this.status,
                            statusText: xhr.statusText
                        });
                    }
                    response = xhr.responseText;
                    if (xhr.getResponseHeader("content-type") && ~xhr.getResponseHeader("content-type").indexOf("application/json")) {
                        try {
                            response = JSON.parse(response);
                        } catch (_error) {
                            response = "Invalid JSON response from server.";
                        }
                    }
                    if (!((200 <= (_ref = xhr.status) && _ref < 300))) {
                        reject({
                            status: this.status,
                            statusText: xhr.statusText
                        });
                    } else {
                        _this._finished(response);
                        resolve(xhr.response);
                    }
                };
                xhr.onerror = function () {
                    reject({
                        status: this.status,
                        statusText: xhr.statusText
                    });
                };
                progressObj = (_ref = xhr.upload) != null ? _ref : xhr;
                progressObj.onprogress = updateProgress;
                headers = {
                    "Accept": "application/json",
                    "Cache-Control": "no-cache",
                    "X-Requested-With": "XMLHttpRequest"
                };
                if (_this.options.headers) {
                    extend(headers, _this.options.headers);
                }
                for (headerName in headers) {
                    headerValue = headers[headerName];
                    if (headerValue) {
                        xhr.setRequestHeader(headerName, headerValue);
                    }
                }
                formData = new FormData();
                if (_this.params) {
                    _ref1 = _this.params;
                    for (key in _ref1) {
                        value = _ref1[key];
                        formData.append(key, value);
                    }
                }

                _requests.push(xhr);

                _this.submitRequest(xhr, formData);
            });
        })(this);
    };

    LinkChecker.prototype.submitRequest = function (xhr, formData) {
        xhr.send(formData);
    };

    LinkChecker.prototype._finished = function (responseText) {
        if (typeof responseText.result != 'undefined' && "html" in responseText.result) {
            this.target.innerHTML = responseText.result.html;
            return true;
        }

        this.target.innerHTML = '-';

        return false;
    };

    LinkCheckerRegistry = {
        init: function () {
            this.register();
        },
        abort: function () {
            this.stopRecursion = true;
        },
        runRecursiveFunction: function(func, arguments, callback) {
            if (arguments.length < 1)
            {
                if (typeof callback !== 'undefined')
                {
                    callback();
                }

                return;
            }

            var argument = arguments[0],
                remainingArguments = Array.prototype.slice.call(arguments,1);

            func(argument, remainingArguments, callback);
        },
        register: function () {

            var self = this,
                elements = document.querySelectorAll('[data-linkchecker]'),
                config = {};

            (function (_this){

                function myFunc(element, remainingElements, callback) {

                    if(_this.stopRecursion){

                        for (var i = 0, len = _requests.length; i < len; i++) {
                            var xhr = _requests[i];
                            xhr.abort();
                        }

                        return;
                    }

                    // do not attach linkchecker again
                    if (typeof element.linkchecker != 'undefined')
                    {
                        _this.runRecursiveFunction(myFunc, remainingElements, callback);
                        return;
                    }

                    var lc = new LinkChecker(element, config);

                    lc.test().then(function(){
                        _this.runRecursiveFunction(myFunc, remainingElements, callback);
                    }).catch(function (err) {
                        _this.runRecursiveFunction(myFunc, remainingElements, callback);
                    });
                }

                _this.runRecursiveFunction(myFunc, elements);
            })(this);
        }
    };

    document.addEventListener('DOMContentLoaded', function(event) {
        LinkCheckerRegistry.init();
    });

    window.addEventListener('beforeunload', function(event) {
        LinkCheckerRegistry.abort();
    });

    // jquery support
    if (window.jQuery) {
        jQuery(document).ajaxComplete(function() {
            LinkCheckerRegistry.init();
        });
    }

    // mootools support
    if (window.MooTools) {
        window.addEvent('ajax_change', function() {
            LinkCheckerRegistry.init();
        });
    }

}).call(this);
