package main

import (
	"encoding/json"
	"fmt"
	"regexp"
	"strings"
	"unicode/utf8"
)

type Result []Token

const (
	AQL_STR = iota
	AQL_KEYWD
	AQL_FIELD
	AQL_ARG
	AQL_OP
	AQL_FUNC
	VAR_DELIMITER   = "`"
	FUNCTION_PARENS = "()"
)

// The extra `json:` crap is to make sure it gets lower cased
type Response struct {
	Alfred string            `json:"alfred"`
	Key    string            `json:"key"`
	Method string            `json:"method"`
	Params map[string]string `json:"params"`
}

type Token struct {
	Text string
	Type int
}

type Param struct {
	Key   string
	Value string
}

type Query struct {
	Table  string
	Fields []string
	Where  map[string]string
}

func lg(str string) {
	fmt.Println(str)
}

func nextChar(str string) (string, string) {
	var ret string
	first, _ := utf8.DecodeRune([]byte(str))
	if len(str) > 1 {
		ret = str[1:]
	} else {
		ret = ""
	}
	return string(first), ret
}

func peekChar(str string) string {
	first, _ := utf8.DecodeRune([]byte(str))
	return string(first)
}

func isValidChar(str string) (ret bool) {
	reg, err := regexp.Compile("[0-9a-zA-Z_]")
	ret = false
	if err != nil {
		fmt.Println("Error in regexp.")
	} else {
		ret = reg.Match([]byte(str))
	}

	return
}

func isOper(str string) (ret bool) {
	reg, err := regexp.Compile("[=\\.,;]")
	ret = false
	if err != nil {
		fmt.Println("Error in regexp.")
	} else {
		ret = reg.Match([]byte(str))
	}

	return
}

func isSpace(str string) (ret bool) {
	reg, err := regexp.Compile("\\s")
	ret = false
	if err != nil {
		fmt.Println("Error in regexp.")
	} else {
		ret = reg.Match([]byte(str))
	}

	return
}

func contains(arr []string, val string) bool {
	for _, a := range arr {
		if a == val {
			return true
		}
	}
	return false
}

func niceString(str string) (ret string) {
	ret = str
	reg, err := regexp.Compile("[\n\t]")
	if err != nil {
		fmt.Println("Error in regexp.")
	} else {
		ret = string(reg.ReplaceAll([]byte(str), []byte("")))
	}

	return
}

func (r *Result) addToken(tokText string, tokType int) {
	*r = append(*r, Token{Type: tokType, Text: tokText})
}

func (r *Result) nextToken() Token {
	ret := (*r)[0]
	*r = (*r)[1:]
	return ret
}

func (r *Result) peekToken() Token {
	return (*r)[0]
}

func (t *Result) hasNextToken() bool {
	return len(*t) != 0
}

func (t *Token) tokenTextEq(text string) bool {
	return t.Text == text || strings.ToLower(t.Text) == text
}

func parseAQL(command string, apiKey string) (string, []string) {
	input := niceString(command)

	keywords := []string{"select", "insert", "delete", "from", "where", "and", "or"}

	var methods = map[string]string{
		"weather":              "Location.Weather",
		"directions":           "Location.Directions",
		"iplookup":             "Location.IPLookup",
		"minecraft_motd":       "Minecraft.MOTD",
		"minecraft_players":    "Minecraft.Players",
		"minecraft_maxplayers": "Minecraft.MaxPlayers",
		"bitbucket_status":     "Net.Bitbucket.Status",
		"github_status":        "Net.Github.Status",
		"heroku_status":        "Net.Heroku.Status",
		"movie":                "Net.TMDB.Movie",
		"ping":                 "Net.Ping",
		"dns":                  "Net.DNS",
		"shorten":              "Net.Shorten",
		"lmgtfy":               "Net.LMGTFY",
		"tasks":                "Tasks.List",
	}

	var tokens Result
	var query Query
	query.Where = make(map[string]string)

	for len(input) > 0 {
		tokText := ""
		tokType := AQL_STR

		if isSpace(peekChar(input)) {
			_, input = nextChar(input)
		} else if isOper(peekChar(input)) {
			tokText, input = nextChar(input)
			tokens.addToken(tokText, AQL_OP)
		} else if peekChar(input) == VAR_DELIMITER {
			_, input = nextChar(input)

			for peekChar(input) != VAR_DELIMITER && len(input) > 0 {
				var n string
				n, input = nextChar(input)
				tokText += n
			}

			_, input = nextChar(input)
			tokens.addToken(tokText, AQL_FIELD)
		} else if peekChar(input) == "'" {
			_, input = nextChar(input)

			for peekChar(input) != "'" && len(input) > 0 {
				var n string
				n, input = nextChar(input)
				tokText += n
			}

			_, input = nextChar(input)
			tokens.addToken(tokText, AQL_ARG)
		} else {
			for len(input) > 0 && isValidChar(peekChar(input)) {
				var n string
				n, input = nextChar(input)
				tokText += n

				if input[0:2] == "()" {
					tokType = AQL_FUNC
					_, input = nextChar(input)
					_, input = nextChar(input)
					break
				}
			}

			if contains(keywords, tokText) || contains(keywords, strings.ToLower(tokText)) {
				tokens.addToken(tokText, AQL_KEYWD)
			} else {
				tokens.addToken(tokText, tokType)
			}
		}
	}

	for tokens.hasNextToken() {
		token := tokens.nextToken()

		if token.Type == AQL_KEYWD && token.tokenTextEq("select") {
			for tokens.hasNextToken() {
				var nToken Token
				nToken = tokens.nextToken()
				query.Fields = append(query.Fields, nToken.Text)
				if tokens.peekToken().Type != AQL_KEYWD {
					tokens.nextToken()
				} else {
					break
				}
			}
		} else if token.Type == AQL_KEYWD && token.tokenTextEq("from") {
			var nToken Token
			nToken = tokens.nextToken()
			query.Table = nToken.Text
		} else if token.Type == AQL_KEYWD && token.tokenTextEq("where") {
			for tokens.hasNextToken() {
				peek := tokens.peekToken()

				if peek.Type == AQL_OP && peek.Text == ";" {
					tokens.nextToken()
					break
				} else if len(tokens) > 5 {
					var param Token
					var next Token
					param = tokens.nextToken()
					tokens.nextToken()
					next = tokens.nextToken()

					if next.Type == AQL_FUNC && next.Text == "me" {
						tokens.nextToken()
						tokens.nextToken()

						peek = tokens.peekToken()

						if peek.Type == AQL_KEYWD && peek.Text == "and" {
							tokens.nextToken()
						} else if peek.Type == AQL_OP && peek.Text == ";" {
							tokens.nextToken()
							break
						}
					} else {
						query.Where[param.Text] = next.Text

						peek = tokens.peekToken()

						if peek.Type == AQL_KEYWD && peek.tokenTextEq("and") {
							tokens.nextToken()
						} else if peek.Type == AQL_OP && peek.Text == ";" {
							tokens.nextToken()
						}
					}
				} else if len(tokens) > 3 {
					var param Token
					var next Token

					param = tokens.nextToken()
					tokens.nextToken()
					next = tokens.nextToken()

					query.Where[param.Text] = next.Text

					if peek.Type == AQL_KEYWD && peek.tokenTextEq("and") {
						tokens.nextToken()
					} else if peek.Type == AQL_OP && peek.Text == ";" {
						tokens.nextToken()
						break
					}
				} else {
					break
				}
			}
		}
	}

	alfredStr, _ := json.Marshal(Response{Alfred: "0.1", Key: apiKey, Method: methods[query.Table], Params: query.Where})

	return string(alfredStr), query.Fields
}

func main() {
	command := "SELECT `location` FROM `weather` WHERE `zip`='48195';"
	fmt.Println("in:", command)
	out, returns := parseAQL(command, "")
	fmt.Println("out:", out)
	fmt.Println("returns:")
	for _, f := range returns {
		fmt.Println(f)
	}
}
