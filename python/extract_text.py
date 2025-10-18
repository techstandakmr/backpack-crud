# # this is what I tried fisrt, resulting the extracted text and displaying them as raw text    
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


#surya OCR
# from surya.table_rec import TableRecPredictor
# from PIL import Image
# import fitz  # PyMuPDF to read PDFs
# import csv

# # Function to convert PDF pages to images
# def pdf_to_images(pdf_path):
#     images = []
#     pdf_doc = fitz.open(pdf_path)
#     for page_num in range(len(pdf_doc)):
#         page = pdf_doc[page_num]
#         pix = page.get_pixmap(dpi=300)
#         img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
#         images.append(img)
#     return images

# # Path to your file
# file_path = "777.pdf"  # or an image file

# # Convert PDF to images (skip if your file is an image)
# if file_path.lower().endswith(".pdf"):
#     images = pdf_to_images(file_path)
# else:
#     images = [Image.open(file_path)]

# # Initialize Surya Table Recognition
# table_rec_predictor = TableRecPredictor()

# all_tables = []

# # Run table recognition on each page
# for img in images:
#     tables = table_rec_predictor([img])
#     all_tables.extend(tables)

# # Print tables (handle tuple cells)
# for i, table in enumerate(all_tables):
#     print(f"\nTable {i+1}:")
#     for row in table.cells:
#         # if cell is a tuple, text is at index 0
#         print([cell[0] if isinstance(cell, tuple) else cell.text for cell in row])

# # Save tables to CSV
# for idx, table in enumerate(all_tables):
#     csv_file = f"output_table_{idx+1}.csv"
#     with open(csv_file, mode='w', newline='', encoding='utf-8') as f:
#         writer = csv.writer(f)
#         for row in table.cells:
#             writer.writerow([cell[0] if isinstance(cell, tuple) else cell.text for cell in row])
#     print(f"Saved Table {idx+1} to {csv_file}")





# surya 
# import sys
# from surya.table_rec import TableRecPredictor
# from PIL import Image
# import fitz  # PyMuPDF to read PDFs
# import json

# def pdf_to_images(pdf_path):
#     images = []
#     pdf_doc = fitz.open(pdf_path)
#     for page_num in range(len(pdf_doc)):
#         page = pdf_doc[page_num]
#         pix = page.get_pixmap(dpi=300)
#         img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
#         images.append(img)
#     return images

# def extract_tables(file_path):
#     # Convert PDF to images or use image directly
#     if file_path.lower().endswith(".pdf"):
#         images = pdf_to_images(file_path)
#     else:
#         images = [Image.open(file_path)]

#     table_rec_predictor = TableRecPredictor()
#     all_tables = []

#     for img in images:
#         tables = table_rec_predictor([img])
#         all_tables.extend(tables)

#     tables_json = []

#     for table in all_tables:
#         table_rows = []
#         for row in table.cells:
#             row_texts = []
#             for cell in row:
#                 # Handle all possible cell formats
#                 if isinstance(cell, tuple):
#                     row_texts.append(cell[0])
#                 elif hasattr(cell, "text_lines"):
#                     row_texts.append(" ".join([line.strip() for line in cell.text_lines]))
#                 elif hasattr(cell, "text"):
#                     row_texts.append(cell.text.strip())
#                 else:
#                     row_texts.append(str(cell))
#             table_rows.append(row_texts)
#         tables_json.append(table_rows)

#     return tables_json


# if __name__ == "__main__":
#     if len(sys.argv) < 2:
#         print(json.dumps({"error": "No file path provided"}))
#         sys.exit(1)

#     file_path = sys.argv[1]
#     try:
#         tables_data = extract_tables(file_path)
#         print(json.dumps({"tables": tables_data}))
#     except Exception as e:
#         print(json.dumps({"error": str(e)}))



# surya text recognition
# from PIL import Image
# from surya.foundation import FoundationPredictor
# from surya.recognition import RecognitionPredictor
# from surya.detection import DetectionPredictor
# import json
# import fitz  # for PDFs

# def pdf_to_images(pdf_path):
#     images = []
#     pdf_doc = fitz.open(pdf_path)
#     for page_num in range(len(pdf_doc)):
#         page = pdf_doc[page_num]
#         pix = page.get_pixmap(dpi=300)
#         img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
#         images.append(img)
#     return images

# def extract_text(file_path):
#     # Convert PDF to images
#     if file_path.lower().endswith(".pdf"):
#         images = pdf_to_images(file_path)
#     else:
#         images = [Image.open(file_path)]

#     # Initialize Surya OCR
#     foundation_predictor = FoundationPredictor()
#     recognition_predictor = RecognitionPredictor(foundation_predictor)
#     detection_predictor = DetectionPredictor()

#     results = []

#     for img in images:
#         predictions = recognition_predictor([img], det_predictor=detection_predictor)
#         page_text_lines = []

#         for line in predictions[0].lines:  # predictions[0] is the first image/page
#             # Each line has: text, confidence, polygon, bbox
#             page_text_lines.append({
#                 "text": line.text,
#                 "confidence": line.confidence,
#                 "bbox": line.bbox
#             })
#         results.append(page_text_lines)

#     return results

# if __name__ == "__main__":
#     import sys
#     if len(sys.argv) < 2:
#         print(json.dumps({"error": "No file path provided"}))
#         sys.exit(1)

#     file_path = sys.argv[1]
#     try:
#         ocr_result = extract_text(file_path)
#         print(json.dumps({"pages": ocr_result}))
#     except Exception as e:
#         print(json.dumps({"error": str(e)}))



# surya Layout and reading order
# from PIL import Image
# from surya.foundation import FoundationPredictor
# from surya.layout import LayoutPredictor
# from surya.settings import settings
# import fitz
# import json
# import sys
# import gc

# def extract_layout(file_path):
#     """Run Surya layout detection on image or PDF page by page (memory-efficient)"""
#     # Initialize layout predictor
#     foundation = FoundationPredictor(checkpoint=settings.LAYOUT_MODEL_CHECKPOINT)
#     layout_predictor = LayoutPredictor(foundation)

#     results = []

#     if file_path.lower().endswith(".pdf"):
#         pdf_doc = fitz.open(file_path)
#         for page_num in range(len(pdf_doc)):
#             page = pdf_doc[page_num]

#             # Reduce DPI to save memory
#             pix = page.get_pixmap(dpi=120)
#             img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)

#             layout_result = layout_predictor([img])[0]  # LayoutResult object

#             page_items = []
#             for box in layout_result.bboxes:
#                 page_items.append({
#                     "bbox": box.bbox,
#                     "polygon": box.polygon,
#                     "label": box.label,
#                     "confidence": float(box.confidence),
#                     "position": getattr(box, "position", None)
#                 })

#             results.append({"page": page_num + 1, "items": page_items})

#             # Free memory
#             del img
#             gc.collect()

#     else:
#         img = Image.open(file_path)
#         layout_result = layout_predictor([img])[0]

#         page_items = []
#         for box in layout_result.bboxes:
#             page_items.append({
#                 "bbox": box.bbox,
#                 "polygon": box.polygon,
#                 "label": box.label,
#                 "confidence": float(box.confidence),
#                 "position": getattr(box, "position", None)
#             })

#         results.append({"page": 1, "items": page_items})
#         del img
#         gc.collect()

#     return results

# if __name__ == "__main__":
#     if len(sys.argv) < 2:
#         print(json.dumps({"error": "No file path provided"}))
#         sys.exit(1)

#     file_path = sys.argv[1]
#     try:
#         layout_data = extract_layout(file_path)
#         print(json.dumps({"layout": layout_data}, indent=2, ensure_ascii=False))
#     except Exception as e:
#         print(json.dumps({"error": str(e)}))




# Table Recognition
from PIL import Image
from surya.table_rec import TableRecPredictor
from surya.foundation import FoundationPredictor
from surya.recognition import RecognitionPredictor
from surya.detection import DetectionPredictor
import fitz
import json
import sys
import gc

def pdf_to_images(pdf_path, dpi=150):
    images = []
    pdf_doc = fitz.open(pdf_path)
    for page in pdf_doc:
        pix = page.get_pixmap(dpi=dpi)
        img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
        images.append(img)
    return images

def extract_table_text(file_path):
    # Initialize Surya predictors
    foundation = FoundationPredictor()
    recognition = RecognitionPredictor(foundation)
    detection = DetectionPredictor()
    table_rec = TableRecPredictor()

    results = []

    # Convert PDF or open image
    if file_path.lower().endswith(".pdf"):
        images = pdf_to_images(file_path)
    else:
        images = [Image.open(file_path)]

    for page_num, img in enumerate(images):
        # Detect tables
        tables = table_rec([img])

        page_data = []
        for table_idx, table in enumerate(tables):
            cells_data = []
            for cell in table.cells:
                # Crop cell image using bbox
                x1, y1, x2, y2 = map(int, cell.bbox)
                cell_img = img.crop((x1, y1, x2, y2))

                # OCR the cell
                text_result = recognition([cell_img], det_predictor=detection)
                text_lines = [line.text for line in text_result[0].lines]
                cell_text = " ".join(text_lines)

                cells_data.append({
                    "row_id": cell.row_id,
                    "col_id": cell.col_id,
                    "text": cell_text,
                    "bbox": cell.bbox,
                    "rowspan": getattr(cell, "rowspan", 1),
                    "colspan": getattr(cell, "colspan", 1),
                    "is_header": getattr(cell, "is_header", False)
                })

            page_data.append({
                "table_idx": table_idx,
                "cells": cells_data
            })
            del cell_img
            gc.collect()

        results.append({"page": page_num + 1, "tables": page_data})
        del img
        gc.collect()

    return results

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No file path provided"}))
        sys.exit(1)

    file_path = sys.argv[1]
    try:
        table_text_data = extract_table_text(file_path)
        print(json.dumps({"tables": table_text_data}, indent=2, ensure_ascii=False))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
