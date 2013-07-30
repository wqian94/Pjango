all: 
	php documentationCompiler.php .

clean: 
	php documentationCleaner.php .

compiler:
	gcc documentationCompiler.c -o documentationCompiler
	chmod +x documentationCompiler

documentation:
	./documentationCompiler .
