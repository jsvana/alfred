var parseAQL = function(command, apiKey) {
    var Type = {
        STR: "str",
        KEYWD: "keywd",
        FIELD: "field",
        ARG: "arg",
        OP: "op",
        FUNC: "func"
    };

    var keywords = ['select', 'insert', 'delete', 'from', 'where', 'and', 'or'];

    var lg = function(str) {
        console.log(str);
    };

    String.prototype.nextChar = function() {
      var first = this.charAt(0);
      return { h: first, t: this.slice(1)};
    };

    String.prototype.peek = function(len) {
        if(typeof len === "undefined") {
            return this.charAt(0);
        } else {
            return this.substring(0, len);
        }
    };

    String.prototype.isValidChar = function() {
        return (/[0-9a-zA-Z_]/).test(this);
    };

    String.prototype.isOper = function() {
        return (/[=\.,;]/).test(this);
    };

    Array.prototype.contains = function(obj) {
        for(var i = 0; i < this.length; i++) {
            if(this[i] === obj) {
                return true;
            }
        }

        return false;
    };

    var turnToString = function(input) {
        return input.replace(/[\n\s]+/g, ' ').toLowerCase();
    };

    var input = turnToString(command);
    //lg("\"" + input + "\"");

    var index = 0;

    var tokens = [];

    var addToken = function(str, type) {
        tokens.push({str: str, type: type});
    };

    while(input.length > 0) {
        var tok = '';
        var type = Type.STR;
        if((/\s/).test(input.peek())) {
            input = input.nextChar().t;
        } else if(input.peek().isOper()) {
            var n = input.nextChar();
            tok = n.h;
            input = n.t;
            addToken(tok, Type.OP);
        } else if(input.peek() === '`') {
            input = input.nextChar().t;
            var tok = '';

            while(input.peek() !== '`' && input.length > 0) {
                var n = input.nextChar();
                tok += n.h;
                input = n.t;
            }
            input = input.nextChar().t;
            addToken(tok, Type.FIELD);
        } else if(input.peek() === "'") {
            input = input.nextChar().t;
            var tok = '';

            while(input.peek() !== "'" && input.length > 0) {
                var n = input.nextChar();
                tok += n.h;
                input = n.t;
            }
            input = input.nextChar().t;
            addToken(tok, Type.ARG);
        } else {
            do {
                var n = input.nextChar();
                tok += n.h;
                input = n.t;
                if(input.peek(2) === "()") {
                    type = Type.FUNC;
                    input = input.nextChar().t;
                    input = input.nextChar().t;
                    break;
                }
            } while(input.length > 0 && input.peek().isValidChar());

            if(keywords.contains(tok)) {
                addToken(tok, Type.KEYWD);
            } else {
                addToken(tok, type);
            }
        }
    }

    //lg(tokens);

    var query = {
        table: '',
        fields: [],
        where: []
    };

    var nextToken = function() {
        return tokens.shift();
    };

    var peekToken = function() {
        return tokens[0];
    };

    var hasNextToken = function() {
        return tokens.length !== 0;
    };

    var tokenCount = function() {
        return tokens.length;
    };

    while(hasNextToken()) {
        var token = nextToken();

        if(token.type === Type.KEYWD && token.str === "select") {
            while(hasNextToken()) {
                query.fields.push(nextToken().str);
                if(peekToken().type !== Type.KEYWD) {
                    nextToken();
                } else {
                    break;
                }
            }
        } else if(token.type === Type.KEYWD && token.str === "from") {
            query.table = nextToken().str;
        } else if(token.type === Type.KEYWD && token.str === "where") {
            while(hasNextToken()) {
                var peek = peekToken();
                if(peek.type === Type.OP && peek.str === ";") {
                    nextToken();
                    break;
                } else if(tokenCount() > 5) {
                    var param = nextToken();
                    nextToken();
                    var next = nextToken();
                    if(next.type === Type.FUNC && next.str === "me") {
                        nextToken();
                        nextToken();

                        var peek = peekToken();
                        if(peek.type === Type.KEYWD && peek.str === "and") {
                            nextToken();
                        } else if(peek.type === Type.OP && peek.str === ";") {
                            nextToken();
                            break;
                        }
                    } else {
                        query.where.push({param: param.str, value: next.str});
                        var peek = peekToken();

                        if(peek.type === Type.KEYWD && peek.str === "and") {
                            nextToken();
                        } else if(peek.type === Type.OP && peek.str === ";") {
                            nextToken();
                            break;
                        }
                    }
                } else if(tokenCount() > 3) {
                    var param = nextToken();
                    nextToken();
                    var value = nextToken();
                    query.where.push({param: param.str, value: value.str});

                    var peek = peekToken();
                    if(peek.type === Type.KEYWD && peek.str === "and") {
                        nextToken();
                    } else if(peek.type === Type.OP && peek.str === ";") {
                        nextToken();
                        break;
                    }
                }
            }
        }
    }

    //lg(query);

    var methods = {
        weather: 'Location.Weather',
        directions: 'Location.Directions',
				iplookup: 'Location.IPLookup',
				minecraft_motd: 'Minecraft.MOTD',
				minecraft_players: 'Minecraft.Players',
				minecraft_maxplayers: 'Minecraft.MaxPlayers',
				bitbucket_status: 'Net.Bitbucket.Status',
				github_status: 'Net.Github.Status',
				heroku_status: 'Net.Heroku.Status',
				movie: 'Net.TMDB.Movie',
				ping: 'Net.Ping',
				dns: 'Net.DNS',
				shorten: 'Net.Shorten',
				lmgtfy: 'Net.LMGTFY',
				tasks: 'Tasks.List'
    };

    var alfredStr = '{"alfred":"0.1","key":"' + apiKey + '","method":"' + methods[query.table] + '","params":{';

    for(var i = 0; i < query.where.length; i++) {
        alfredStr += '"' + query.where[i].param + '":"' + query.where[i].value + '"';
        if(i < query.where.length - 1) {
            alfredStr += ',';
        }
    }

    alfredStr += '}}';

    return { query: alfredStr, returns: query.fields };
};

var query = function(command, apiKey, callback) {
    var req = parseAQL(command, apiKey);

    $.ajax({
        data: req.query,
        success: function(data) {
            var ret = {};
            for(var i = 0; i < req.returns.length; i++) {
                ret[req.returns[i]] = data.data[req.returns[i]];
            }

            callback(ret);
        }
    });
};

//lg(alfredStr);
