from flask import Flask, render_template, send_file, request, abort
import os, io, zipfile

app = Flask(__name__)

# Caminho UNC do NAS
BASE_DIR = r' REPOSITÓRIO '

# Função para construir caminho absoluto
def get_path(*parts):
    return os.path.join(BASE_DIR, *parts)

# Lista subpastas de um diretório
def list_subfolders(folder_path):
    return [f for f in os.listdir(folder_path) if os.path.isdir(os.path.join(folder_path, f))]

# Lista PDFs de um diretório
def list_pdfs(folder_path):
    return [f for f in os.listdir(folder_path) if f.lower().endswith('.pdf')]

# Constrói árvore completa para sidebar (accordion)
def build_tree(path, prefix=''):
    tree = []
    for f in sorted(os.listdir(path)):
        full_path = os.path.join(path, f)
        if os.path.isdir(full_path):
            sub = build_tree(full_path, prefix + f + '/')
            tree.append({'name': f, 'path': prefix + f, 'subfolders': sub})
    return tree

# Página principal e subpastas
@app.route('/')
@app.route('/<path:subpath>')
def index(subpath=''):
    current_path = get_path(subpath)
    if not os.path.exists(current_path):
        abort(404)

    pdfs = list_pdfs(current_path)
    subfolders = list_subfolders(current_path)
    folder_tree = build_tree(BASE_DIR)

    return render_template('index.html',
                           pdfs=pdfs,
                           subfolders=subfolders,
                           folder_tree=folder_tree,
                           subpath=subpath)

# Servir PDF para iframe ou download
@app.route('/view_pdf/<path:filepath>')
def view_pdf(filepath):
    full_path = get_path(filepath)
    if not os.path.isfile(full_path):
        abort(404)
    # Força abrir em iframe sem download automático
    return send_file(full_path, mimetype='application/pdf')

@app.route('/download/<path:filepath>')
def download_file(filepath):
    full_path = get_path(filepath)
    if not os.path.isfile(full_path):
        abort(404)
    return send_file(full_path, as_attachment=True)

# Download em lote (pastas e PDFs)
@app.route('/download_batch', methods=['POST'])
def download_batch():
    selected_paths = request.form.getlist('paths')
    if not selected_paths:
        return "Nenhuma pasta ou arquivo selecionado", 400

    zip_buffer = io.BytesIO()
    with zipfile.ZipFile(zip_buffer, 'w') as zipf:
        for path in selected_paths:
            abs_path = get_path(path)
            if os.path.isfile(abs_path):
                zipf.write(abs_path, arcname=os.path.basename(path))
            elif os.path.isdir(abs_path):
                for root, _, files in os.walk(abs_path):
                    for f in files:
                        if f.lower().endswith('.pdf'):
                            file_path = os.path.join(root, f)
                            arcname = os.path.relpath(file_path, BASE_DIR)
                            zipf.write(file_path, arcname=arcname)
    zip_buffer.seek(0)
    return send_file(zip_buffer, mimetype='application/zip', as_attachment=True, download_name="download_em_lote.zip")

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8080, debug=True)
