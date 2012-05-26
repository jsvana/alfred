PROG = alfred
OBJS = main.o
LIBS = json glib-2.0

CC = gcc
CFLAGS = -ggdb -Wall -ggdb $(shell pkg-config --cflags ${LIBS})
LDFLAGS = $(shell pkg-config --libs ${LIBS}) 

all: ${PROG}

${PROG}: ${OBJS}
	@echo ${CC} -o ${PROG}
	@${CC} ${CFLAGS} -o $@ $+ ${LDFLAGS}

%.o: %.c
	@echo ${CC} -c $<
	@${CC} ${CFLAGS} -c $< -o $@

run: all
	./${PROG}

install: all
	@echo Installing to /usr/local/bin
	@install -t /usr/local/bin ${PROG}

clean:
	rm -f ${PROG} ${OBJS}

.PHONY: all clean install
