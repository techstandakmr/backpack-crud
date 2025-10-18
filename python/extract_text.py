# # real
# import sys
# import fitz # PyMuPDF
# from PIL import Image
# import pytesseract
# import io

# # Tesseract path (Windows)
# pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

# # Force UTF-8 stdout
# import io
# sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')


# def extract_text(pdf_path):
#     text_output = ""
#     with fitz.open(pdf_path) as doc:
#         for page_num, page in enumerate(doc, start=1):
#             text = page.get_text()
            
#             if text.strip():  # text-based page
#                 text_output += f"\n\n--- Page {page_num} ---\n\n{text.strip()}\n"
#             else:  # image-based page, do OCR
#                 pix = page.get_pixmap()
#                 img = Image.open(io.BytesIO(pix.tobytes()))
                
#                 # OCR with English + Arabic
#                 ocr_text = pytesseract.image_to_string(img, lang="eng+ara")
                
#                 text_output += f"\n\n--- Page {page_num} (OCR) ---\n\n{ocr_text.strip()}\n"
#     return text_output.strip()


# if __name__ == "__main__":
#     if len(sys.argv) < 2:
#         print("Usage: python extract_text.py <path_to_pdf>")
#         sys.exit(1)

#     pdf_path = sys.argv[1]

#     try:
#         extracted_text = extract_text(pdf_path)
#         if not extracted_text.strip():
#             print("❌ No text found in PDF.")
#         else:
#             print(extracted_text)
#     except Exception as e:
#         print(f"❌ Error: {e}")
#         sys.exit(1)



import sys
import fitz
from PIL import Image
import pytesseract
import io
import re
import json

pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

def extract_text(pdf_path):
    text_output = ""
    with fitz.open(pdf_path) as doc:
        for page in doc:
            text = page.get_text()
            if not text.strip():
                pix = page.get_pixmap()
                img = Image.open(io.BytesIO(pix.tobytes()))
                text = pytesseract.image_to_string(img, lang="eng+ara")
            text_output += text + "\n"
    return text_output

def parse_text_to_dict_or_table(raw_text):
    lines = [l.strip() for l in raw_text.splitlines() if l.strip()]
    data = {}
    rows = []

    kv_pattern = re.compile(r'([A-Za-z0-9\s]+)[:\-]\s*([^\n]+)')
    for line in lines:
        kv_match = kv_pattern.match(line)
        if kv_match:
            key = kv_match.group(1).strip().title().replace(" ", "_")
            value = kv_match.group(2).strip()
            data[key] = value
        else:
            parts = re.split(r'\s{2,}|\t+', line)
            if len(parts) > 2:
                rows.append(parts)

    if len(rows) >= 2:
        header = rows[0]
        body = [dict(zip(header, r)) for r in rows[1:] if len(r) == len(header)]
        return {"table": body}

    return {"data": data}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Missing PDF path"}))
        sys.exit(1)

    pdf_path = sys.argv[1]
    try:
        raw_text = extract_text(pdf_path)
        parsed_data = parse_text_to_dict_or_table(raw_text)

        # Always include the raw extracted text
        output = {"raw_text": raw_text.strip()}

        if "table" in parsed_data:
            output["table"] = parsed_data["table"]
        elif "data" in parsed_data and parsed_data["data"]:
            output["data"] = parsed_data["data"]
        else:
            output["message"] = "No structured data found."

        print(json.dumps(output, ensure_ascii=False))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)
