#ifndef ALFRED_ERROR
#define ALFRED_ERROR

typedef enum alfred_error alfred_error;
enum alfred_error {
	ALFRED_ERROR_OK = 0,
	ALFRED_ERROR_MALFORMED_COMMAND = 1,
	ALFRED_ERROR_UNKNOWN_COMMAND = 2,

	// This is for internal use
	ALFRED_ERROR_COUNT = 3,
};

void alfred_error_static(alfred_error err);

#endif
