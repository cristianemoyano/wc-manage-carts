# Nombre del plugin
PLUGIN_NAME = wc-manage-carts

# Archivo ZIP de destino
ZIP_FILE = $(PLUGIN_NAME).zip

# Archivos y directorios para incluir en el ZIP
FILES = \
    wc-manage-carts

# Comando para crear el archivo ZIP
ZIP_COMMAND = zip -r

.PHONY: all zip clean

all: zip

zip:
	@echo "Creando archivo ZIP..."
	@$(ZIP_COMMAND) $(ZIP_FILE) $(FILES)
	@echo "Archivo ZIP creado: $(ZIP_FILE)"

clean:
	@echo "Eliminando archivo ZIP..."
	@rm -f $(ZIP_FILE)
	@echo "Archivo ZIP eliminado: $(ZIP_FILE)"
