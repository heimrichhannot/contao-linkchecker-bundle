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

    // jquery support
    if (window.jQuery) {
        jQuery(document).ready(function () {
            LinkCheckerRegistry.init();
        });

        jQuery(document).ajaxComplete(function () {
            LinkCheckerRegistry.init();
        });

        jQuery(window).on('beforeunload', function () {
            LinkCheckerRegistry.abort();
        });
    }

    // mootools support
    if (window.MooTools) {

        window.addEvent('domready', function () {
            LinkCheckerRegistry.init();
        });

        window.addEvent('ajax_change', function () {
            LinkCheckerRegistry.init();
        });

        window.addEvent('beforeunload', function () {
            LinkCheckerRegistry.abort();
        });
    }

}).call(this);
