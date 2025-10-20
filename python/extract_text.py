# this is what I tried fisrt, resulting the extracted text and displaying them as raw text    
# raw text based
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





# table based
# import sys
# import fitz  # PyMuPDF
# from PIL import Image
# import pytesseract
# import io
# import re
# import json
# import warnings
# import pandas as pd
# from typing import List, Dict, Tuple

# # Suppress warnings
# warnings.filterwarnings('ignore')

# # Tesseract path (Windows)
# pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

# # Force UTF-8 stdout
# sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')


# class PDFTableExtractor:
#     def __init__(self, pdf_path):
#         self.pdf_path = pdf_path
#         self.extracted_tables = []
        
#     def extract_with_camelot(self):
#         """Try to extract tables using Camelot (works best with text-based PDFs)"""
#         try:
#             import camelot
            
#             # Try lattice mode first (for bordered tables)
#             tables = camelot.read_pdf(self.pdf_path, pages='all', flavor='lattice')
#             if len(tables) == 0:
#                 # Try stream mode (for non-bordered tables)
#                 tables = camelot.read_pdf(self.pdf_path, pages='all', flavor='stream')
            
#             for i, table in enumerate(tables):
#                 df = table.df
                
#                 # Clean and process the dataframe
#                 processed_table = self._process_camelot_table(df, table.page)
#                 if processed_table:
#                     self.extracted_tables.append(processed_table)
                    
#             return len(self.extracted_tables) > 0
#         except Exception as e:
#             # Silently fail and try other methods
#             return False
    
#     def _process_camelot_table(self, df, page_num):
#         """Process and clean Camelot extracted table"""
#         try:
#             # Remove completely empty rows and columns
#             df = df.dropna(how='all').dropna(axis=1, how='all')
            
#             if df.empty:
#                 return None
            
#             # Find the actual header row (look for row with most non-empty cells)
#             header_row_idx = 0
#             max_filled = 0
            
#             for idx in range(min(3, len(df))):  # Check first 3 rows
#                 filled = df.iloc[idx].notna().sum()
#                 if filled > max_filled:
#                     max_filled = filled
#                     header_row_idx = idx
            
#             # Set header
#             headers = df.iloc[header_row_idx].tolist()
#             df = df.iloc[header_row_idx + 1:]
            
#             # Clean headers
#             clean_headers = []
#             seen_headers = {}
            
#             for i, h in enumerate(headers):
#                 # Convert to string and clean
#                 h_str = str(h).strip() if pd.notna(h) else f"Column_{i+1}"
                
#                 # Remove excessive whitespace and newlines
#                 h_str = ' '.join(h_str.split())
                
#                 # If header is too long or contains too much data, simplify it
#                 if len(h_str) > 50 or '\n' in str(headers[i]):
#                     # Try to extract just the field name
#                     parts = str(headers[i]).split('\n')
#                     # Look for patterns like "Field Name:" or just use first part
#                     for part in parts:
#                         if ':' in part:
#                             potential_header = part.split(':')[0].strip()
#                             if len(potential_header) < 50 and potential_header:
#                                 h_str = potential_header
#                                 break
#                     else:
#                         # Use first meaningful part
#                         h_str = parts[0].strip()[:50] if parts[0].strip() else f"Column_{i+1}"
                
#                 # Handle duplicate headers
#                 if h_str in seen_headers:
#                     seen_headers[h_str] += 1
#                     h_str = f"{h_str}_{seen_headers[h_str]}"
#                 else:
#                     seen_headers[h_str] = 0
                
#                 clean_headers.append(h_str)
            
#             df.columns = clean_headers
            
#             # Clean cell values
#             df = df.map(lambda x: str(x).strip() if pd.notna(x) and str(x).strip() else '')
            
#             # Remove rows where all cells are empty
#             df = df[df.astype(str).ne('').any(axis=1)]
            
#             # Reset index
#             df = df.reset_index(drop=True)
            
#             if df.empty:
#                 return None
            
#             return {
#                 'method': 'camelot',
#                 'page': page_num,
#                 'data': df.to_dict('records'),
#                 'headers': clean_headers
#             }
            
#         except Exception as e:
#             return None
    
#     def extract_with_ocr_and_pattern(self):
#         """Extract using OCR and intelligent pattern matching"""
#         with fitz.open(self.pdf_path) as doc:
#             for page_num, page in enumerate(doc, start=1):
#                 # Get text (if available)
#                 text = page.get_text()
                
#                 # If no text, use OCR
#                 if not text.strip():
#                     pix = page.get_pixmap()
#                     img = Image.open(io.BytesIO(pix.tobytes()))
#                     text = pytesseract.image_to_string(img, lang="eng+ara")
                
#                 if text.strip():
#                     # Try to identify and extract table structures
#                     tables = self._parse_text_to_table(text, page_num)
#                     self.extracted_tables.extend(tables)
    
#     def _parse_text_to_table(self, text: str, page_num: int) -> List[Dict]:
#         """Parse text into structured table format using intelligent pattern matching"""
#         tables = []
        
#         # Split into lines
#         lines = [line.strip() for line in text.split('\n') if line.strip()]
        
#         # Method 1: Detect key-value pairs (common in forms)
#         kv_table = self._extract_key_value_pairs(lines)
#         if kv_table and len(kv_table) > 3:  # Only if we found enough data
#             tables.append({
#                 'method': 'key_value',
#                 'page': page_num,
#                 'data': kv_table,
#                 'headers': ['Field', 'Value']
#             })
        
#         return tables
    
#     def _extract_key_value_pairs(self, lines: List[str]) -> List[Dict]:
#         """Extract key-value pairs like 'Customer Name: John Doe'"""
#         data = []
        
#         # Common separators
#         patterns = [
#             r'^([^:]+):\s*(.+)$',  # Colon separator
#             r'^([^-]+)-\s*(.+)$',   # Dash separator
#             r'^([^=]+)=\s*(.+)$',   # Equal separator
#         ]
        
#         for line in lines:
#             # Skip lines that are too long (likely not field names)
#             if len(line) > 200:
#                 continue
                
#             for pattern in patterns:
#                 match = re.match(pattern, line)
#                 if match:
#                     key = match.group(1).strip()
#                     value = match.group(2).strip()
                    
#                     # Filter out very short keys or keys with too many special chars
#                     if 3 <= len(key) <= 100 and value and len(value) <= 500:
#                         # Skip if key has too many numbers (likely data, not field name)
#                         num_count = sum(c.isdigit() for c in key)
#                         if num_count / len(key) < 0.5:  # Less than 50% numbers
#                             data.append({
#                                 'Field': key,
#                                 'Value': value
#                             })
#                     break
        
#         return data if len(data) > 0 else []
    
#     def get_results(self) -> Dict:
#         """Get all extracted tables in a structured format"""
#         return {
#             'success': len(self.extracted_tables) > 0,
#             'total_tables': len(self.extracted_tables),
#             'tables': self.extracted_tables
#         }


# def main():
#     if len(sys.argv) < 2:
#         result = {
#             'success': False,
#             'error': 'Usage: python extract_text.py <path_to_pdf>'
#         }
#         print(json.dumps(result, ensure_ascii=False))
#         sys.exit(1)

#     pdf_path = sys.argv[1]

#     try:
#         extractor = PDFTableExtractor(pdf_path)
        
#         # Try Camelot first (best for structured tables)
#         success = extractor.extract_with_camelot()
        
#         # If Camelot fails or finds nothing, use OCR + pattern matching
#         if not success:
#             extractor.extract_with_ocr_and_pattern()
        
#         # Get results
#         results = extractor.get_results()
        
#         if results['success']:
#             # Output as JSON for easy parsing in PHP
#             print(json.dumps(results, ensure_ascii=False, indent=2))
#         else:
#             print(json.dumps({
#                 'success': False,
#                 'error': 'No tables could be extracted from the PDF'
#             }, ensure_ascii=False))
    
#     except Exception as e:
#         print(json.dumps({
#             'success': False,
#             'error': f'Error processing PDF: {str(e)}'
#         }, ensure_ascii=False))
#         sys.exit(1)


# if __name__ == "__main__":
#     main()



import sys
import json
import io
import warnings

# Suppress all warnings
warnings.filterwarnings('ignore')

# Force UTF-8 stdout
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

def extract_all_content(pdf_path):
    """Main function to extract both text and tables"""
    result = {
        'status': 'success',
        'tables': [],
        'text': [],
        'summary': {
            'total_pages': 0,
            'total_tables': 0,
            'total_text_blocks': 0
        }
    }
    
    try:
        import fitz  # PyMuPDF
        from PIL import Image
        import pytesseract
        import camelot
        
        # Set Tesseract path
        pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"
        
        # Extract tables first using Camelot
        try:
            # Try lattice mode first (for bordered tables)
            tables = camelot.read_pdf(pdf_path, pages='all', flavor='lattice', suppress_stdout=True)
            
            # If no tables found, try stream mode (for borderless tables)
            if len(tables) == 0:
                tables = camelot.read_pdf(pdf_path, pages='all', flavor='stream', suppress_stdout=True)
            
            for i, table in enumerate(tables):
                # Convert DataFrame to list format
                df = table.df
                headers = df.iloc[0].tolist() if len(df) > 0 else []
                rows = df.iloc[1:].values.tolist() if len(df) > 1 else []
                
                table_dict = {
                    'table_number': i + 1,
                    'page': table.page,
                    'headers': headers,
                    'rows': rows
                }
                result['tables'].append(table_dict)
                
            result['summary']['total_tables'] = len(tables)
        except Exception as table_error:
            # If Camelot fails, continue without tables
            result['tables'] = []
            result['summary']['total_tables'] = 0
        
        # Extract text from PDF
        with fitz.open(pdf_path) as doc:
            result['summary']['total_pages'] = len(doc)
            
            for page_num, page in enumerate(doc, start=1):
                page_text = page.get_text()
                
                if page_text.strip():  # text-based page
                    result['text'].append({
                        'page': page_num,
                        'text': page_text.strip(),
                        'type': 'text'
                    })
                else:  # image-based page, do OCR
                    try:
                        pix = page.get_pixmap()
                        img = Image.open(io.BytesIO(pix.tobytes()))
                        
                        # OCR with English + Arabic
                        ocr_text = pytesseract.image_to_string(img, lang="eng+ara")
                        
                        result['text'].append({
                            'page': page_num,
                            'text': ocr_text.strip(),
                            'type': 'ocr'
                        })
                    except Exception as ocr_error:
                        result['text'].append({
                            'page': page_num,
                            'text': f'[OCR Error: {str(ocr_error)}]',
                            'type': 'error'
                        })
            
            result['summary']['total_text_blocks'] = len(result['text'])
        
    except Exception as e:
        result['status'] = 'error'
        result['error'] = str(e)
        result['error_type'] = type(e).__name__
    
    return result

if __name__ == "__main__":
    try:
        if len(sys.argv) < 2:
            output = {
                'status': 'error',
                'error': 'No PDF path provided'
            }
        else:
            pdf_path = sys.argv[1]
            output = extract_all_content(pdf_path)
        
        # Print only JSON output
        print(json.dumps(output, ensure_ascii=False))
        
    except Exception as e:
        error_output = {
            'status': 'error',
            'error': str(e),
            'error_type': type(e).__name__
        }
        print(json.dumps(error_output, ensure_ascii=False))