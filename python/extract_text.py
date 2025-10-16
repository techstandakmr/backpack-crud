# real
import sys
import fitz # PyMuPDF
from PIL import Image
import pytesseract
import io

# Tesseract path (Windows)
pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

# Force UTF-8 stdout
import io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')


def extract_text(pdf_path):
    text_output = ""
    with fitz.open(pdf_path) as doc:
        for page_num, page in enumerate(doc, start=1):
            text = page.get_text()
            
            if text.strip():  # text-based page
                text_output += f"\n\n--- Page {page_num} ---\n\n{text.strip()}\n"
            else:  # image-based page, do OCR
                pix = page.get_pixmap()
                img = Image.open(io.BytesIO(pix.tobytes()))
                
                # OCR with English + Arabic
                ocr_text = pytesseract.image_to_string(img, lang="eng+ara")
                
                text_output += f"\n\n--- Page {page_num} (OCR) ---\n\n{ocr_text.strip()}\n"
    return text_output.strip()


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python extract_text.py <path_to_pdf>")
        sys.exit(1)

    pdf_path = sys.argv[1]

    try:
        extracted_text = extract_text(pdf_path)
        if not extracted_text.strip():
            print("❌ No text found in PDF.")
        else:
            print(extracted_text)
    except Exception as e:
        print(f"❌ Error: {e}")
        sys.exit(1)
