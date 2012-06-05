#include <stdio.h>
#include <json.h>

#include "error.h"
#include "json.h"

static char *alfred_error_text[ALFRED_ERROR_COUNT] = {
	"Ok.",
	"Malformed command.",
	"Unknown command.",
	"Not authenticated.",
	"Incorrect parameters.",
	"Method failed.",
	"Internal server error."
};

void alfred_error_static(alfred_error err) {
	alfred_json_create_display(-err, alfred_error_text[err], NULL);
}
