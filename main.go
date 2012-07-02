package main

import (
	"fmt"
	"unicode/utf8"
	"strings"
	"regexp"
)

const (
	AQL_STR = iota
	AQL_KEYWD
	AQL_FIELD
	AQL_ARG
	AQL_OP
	AQL_FUNC
	VAR_DELIMITER = "`"
	FUNCTION_PARENS = "()"
)

type Token struct {
	Text string
	Type int
}

type Param struct {
	Key string
	Value string
}

type Query struct {
	Table string
	Fields []string
	Where []Param
}

func lg(str string) {
	fmt.Println(str)
}

func nextChar(str string) (string, string) {
	var ret string
	first, _ := utf8.DecodeRune([]byte(str))
	if (len(str) > 1) {
		ret = str[1:]
	} else {
		ret = ""
	}
	return string(first), ret
}

func peekChar(str string) (string) {
	first, _ := utf8.DecodeRune([]byte(str))
	return string(first)
}

func isValidChar(str string) (ret bool) {
	reg, err := regexp.Compile("[0-9a-zA-Z_]")
	ret = false
	if (err != nil) {
		fmt.Println("Error in regexp.")
	} else {
		ret = reg.Match([]byte(str))
	}

	return
}

func isOper(str string) (ret bool) {
	reg, err := regexp.Compile("[=\\.,;]")
	ret = false
	if (err != nil) {
		fmt.Println("Error in regexp.")
	} else {
		ret = reg.Match([]byte(str))
	}

	return
}

func isSpace(str string) (ret bool) {
	reg, err := regexp.Compile("\\s")
	ret = false
	if (err != nil) {
		fmt.Println("Error in regexp.")
	} else {
		ret = reg.Match([]byte(str))
	}

	return
}

func contains(arr []string, val string) (bool) {
	for _, a := range arr {
		if (a == val) {
			return true
		}
	}
	return false
}

func niceString(str string) (ret string) {
	ret = str
	reg, err := regexp.Compile("[\n\t]")
	if (err != nil) {
		fmt.Println("Error in regexp.")
	} else {
		ret = string(reg.ReplaceAll([]byte(str), []byte("")))
	}

	return
}

func addToken(tokens []Token, tokText string, tokType int) ([]Token) {
	var t Token
	t.Type = tokType
	t.Text = tokText
	return append(tokens, t)
}

func nextToken(tokens []Token) (Token, []Token) {
	return tokens[0], tokens[1:]
}

func peekToken(tokens []Token) (Token) {
	return tokens[0]
}

func hasNextToken(tokens []Token) (bool) {
	return len(tokens) != 0
}

func tokenCount(tokens []Token) (int) {
	return len(tokens)
}

func tokenTextEq(token Token, text string) (bool) {
	return token.Text == text || strings.ToLower(token.Text) == text
}

func parseAQL(command string, apiKey string) (string, []string) {
	input := niceString(command)

	keywords := []string{"select", "insert", "delete", "from", "where", "and", "or"}

	var methods = map[string] string {
		"weather": "Location.Weather",
        "directions": "Location.Directions",
        "iplookup": "Location.IPLookup",
        "minecraft_motd": "Minecraft.MOTD",
        "minecraft_players": "Minecraft.Players",
        "minecraft_maxplayers": "Minecraft.MaxPlayers",
        "bitbucket_status": "Net.Bitbucket.Status",
        "github_status": "Net.Github.Status",
        "heroku_status": "Net.Heroku.Status",
        "movie": "Net.TMDB.Movie",
        "ping": "Net.Ping",
        "dns": "Net.DNS",
        "shorten": "Net.Shorten",
        "lmgtfy": "Net.LMGTFY",
        "tasks": "Tasks.List",
	}

	var tokens []Token
	var query Query

	for (len(input) > 0) {
		tokText := ""
		tokType := AQL_STR

		if (isSpace(peekChar(input))) {
			_, input = nextChar(input)
		} else if (isOper(peekChar(input))) {
			tokText, input = nextChar(input)
			tokens = addToken(tokens, tokText, AQL_OP)
		} else if (peekChar(input) == VAR_DELIMITER) {
			_, input = nextChar(input)

			for (peekChar(input) != VAR_DELIMITER && len(input) > 0) {
				var n string
				n, input = nextChar(input)
				tokText += n
			}

			_, input = nextChar(input)
			tokens = addToken(tokens, tokText, AQL_FIELD)
		} else if (peekChar(input) == "'") {
			_, input = nextChar(input)

			for (peekChar(input) != "'" && len(input) > 0) {
				var n string
				n, input = nextChar(input)
				tokText += n
			}

			_, input = nextChar(input)
			tokens = addToken(tokens, tokText, AQL_ARG)
		} else {
			for (len(input) > 0 && isValidChar(peekChar(input))) {
				var n string
				n, input = nextChar(input)
				tokText += n

				if (input[0:2] == "()") {
					tokType = AQL_FUNC
					_, input = nextChar(input)
					_, input = nextChar(input)
					break
				}
			}

			if (contains(keywords, tokText) || contains(keywords, strings.ToLower(tokText))) {
				tokens = addToken(tokens, tokText, AQL_KEYWD)
			} else {
				tokens = addToken(tokens, tokText, tokType)
			}
		}
	}

	for (hasNextToken(tokens)) {
		var token Token
		token, tokens = nextToken(tokens)

		if (token.Type == AQL_KEYWD && tokenTextEq(token, "select")) {
			for hasNextToken(tokens) {
				var nToken Token
				nToken, tokens = nextToken(tokens)
				query.Fields = append(query.Fields, nToken.Text)
				if (peekToken(tokens).Type != AQL_KEYWD) {
					_, tokens = nextToken(tokens)
				} else {
					break
				}
			}
		} else if (token.Type == AQL_KEYWD && tokenTextEq(token, "from")) {
			var nToken Token
			nToken, tokens = nextToken(tokens)
			query.Table = nToken.Text
		} else if (token.Type == AQL_KEYWD && tokenTextEq(token, "where")) {
			for hasNextToken(tokens) {
				peek := peekToken(tokens)

				if (peek.Type == AQL_OP && peek.Text == ";") {
					_, tokens = nextToken(tokens)
					break
				} else if (tokenCount(tokens) > 5) {
					var param Token
					var next Token
					param, tokens = nextToken(tokens)
					_, tokens = nextToken(tokens)
					next, tokens = nextToken(tokens)

					if (next.Type == AQL_FUNC && next.Text == "me") {
						_, tokens = nextToken(tokens)
						_, tokens = nextToken(tokens)

						peek = peekToken(tokens)

						if (peek.Type == AQL_KEYWD && peek.Text == "and") {
							_, tokens = nextToken(tokens)
						} else if (peek.Type == AQL_OP && peek.Text == ";") {
							_, tokens = nextToken(tokens)
							break
						}
					} else {
						var p Param
						p.Key = param.Text
						p.Value = next.Text
						query.Where = append(query.Where, p)

						peek = peekToken(tokens)

						if (peek.Type == AQL_KEYWD && tokenTextEq(peek, "and")) {
							_, tokens = nextToken(tokens)
						} else if (peek.Type == AQL_OP && peek.Text == ";") {
							_, tokens = nextToken(tokens)
						}
					}
				} else if (tokenCount(tokens) > 3) {
					var param Token
					var next Token
					var p Param

					param, tokens = nextToken(tokens)
					_, tokens = nextToken(tokens)
					next, tokens = nextToken(tokens)

					p.Key = param.Text
					p.Value = next.Text

					query.Where = append(query.Where, p)

					if (peek.Type == AQL_KEYWD && tokenTextEq(peek, "and")) {
						_, tokens = nextToken(tokens)
					} else if (peek.Type == AQL_OP && peek.Text == ";") {
						_, tokens = nextToken(tokens)
						break
					}
				} else {
					break
				}
			}
		}
	}

	alfredStr := "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"" + methods[query.Table] + "\",\"params\":{"

	for _, p := range(query.Where) {
		alfredStr += "\"" + p.Key + "\":\"" + p.Value + "\","
	}

	alfredStr = alfredStr[0:len(alfredStr) - 1] + "}}"

	return alfredStr, query.Fields
}

func main() {
	command := "SELECT `location` FROM `weather stuff` WHERE `zip`='48195';"
	fmt.Println("in:", command)
	out, returns := parseAQL(command, "")
	fmt.Println("out:", out)
	fmt.Println("returns:")
	for _, f := range(returns) {
		fmt.Println(f)
	}
}
