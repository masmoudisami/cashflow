#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import sys
import os
from datetime import datetime

# Ajouter le chemin du script au path
script_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, script_dir)

try:
    from reportlab.lib.pagesizes import A4, landscape
    from reportlab.pdfgen import canvas
    from reportlab.lib.units import cm
    from reportlab.lib import colors
    from reportlab.pdfbase import pdfmetrics
    from reportlab.pdfbase.ttfonts import TTFont
    import mysql.connector
except ImportError as e:
    print(f"Import error: {e}", file=sys.stderr)
    sys.exit(1)

def format_number(value):
    """Format number with 3 decimals, space as thousands separator, comma as decimal separator"""
    try:
        num = float(value)
        formatted = "{:,.3f}".format(num).replace(',', 'X').replace('.', ',').replace('X', ' ')
        return formatted
    except:
        return str(value)

def generate_pdf(user_id, username, output_file):
    try:
        print(f"Generating PDF for user_id={user_id}, username={username}", file=sys.stderr)
        print(f"Output file: {output_file}", file=sys.stderr)
        
        # Connexion MySQL
        db = mysql.connector.connect(
            host="localhost",
            user="sami",
            password="Sm/131301",
            database="cashflow_db"
        )
        cursor = db.cursor()
        
        # Récupérer les transactions
        cursor.execute("""
            SELECT t.transaction_date, t.description, t.amount, t.type, t.payment_method, c.name as category 
            FROM transactions t 
            LEFT JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = %s 
            ORDER BY t.transaction_date DESC
        """, (user_id,))
        rows = cursor.fetchall()
        print(f"Found {len(rows)} transactions", file=sys.stderr)
        
        # Récupérer les totaux
        cursor.execute("""
            SELECT 
                SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type='expense' AND is_deferred=0 THEN amount ELSE 0 END) as total_expense_direct,
                SUM(CASE WHEN type='expense' AND is_deferred=1 THEN amount ELSE 0 END) as total_expense_deferred
            FROM transactions 
            WHERE user_id = %s
        """, (user_id,))
        totals = cursor.fetchone()
        
        db.close()
        
        # Création PDF en mode PAYSAGE pour tout tenir sur 1 page
        c = canvas.Canvas(output_file, pagesize=landscape(A4))
        width, height = landscape(A4)
        
        # En-tête
        c.setFont("Helvetica-Bold", 18)
        c.drawString(2*cm, height - 2*cm, f"Rapport de Trésorerie - {username}")
        
        c.setFont("Helvetica", 10)
        c.drawString(2*cm, height - 2.8*cm, f"Date d'émission: {datetime.now().strftime('%Y-%m-%d %H:%M')}")
        
        # Tableau des totaux
        c.setFont("Helvetica-Bold", 11)
        c.drawString(2*cm, height - 4*cm, "Récapitulatif")
        
        c.setFillColor(colors.Color(0.2, 0.3, 0.4))
        c.rect(2*cm, height - 5.2*cm, 8*cm, 1*cm, fill=1)
        c.setFillColor(colors.white)
        c.setFont("Helvetica-Bold", 10)
        c.drawString(2.2*cm, height - 4.7*cm, "Poste")
        c.drawString(5*cm, height - 4.7*cm, "Montant")
        
        c.setFillColor(colors.black)
        c.setFont("Helvetica", 9)
        y = height - 5.5*cm
        
        c.drawString(2.2*cm, y, "Revenus")
        c.drawString(5*cm, y, format_number(totals[0] if totals[0] else 0))
        y -= 0.5*cm
        
        c.drawString(2.2*cm, y, "Dépenses Directes")
        c.drawString(5*cm, y, format_number(totals[1] if totals[1] else 0))
        y -= 0.5*cm
        
        c.drawString(2.2*cm, y, "Dépenses Différées")
        c.drawString(5*cm, y, format_number(totals[2] if totals[2] else 0))
        y -= 0.5*cm
        
        c.setFillColor(colors.Color(0.2, 0.3, 0.4))
        c.rect(2*cm, y, 8*cm, 0.4*cm, fill=1)
        c.setFillColor(colors.white)
        c.setFont("Helvetica-Bold", 10)
        c.drawString(2.2*cm, y + 0.1*cm, "Total Dépenses")
        total_expenses = (totals[1] if totals[1] else 0) + (totals[2] if totals[2] else 0)
        c.drawString(5*cm, y + 0.1*cm, format_number(total_expenses))
        
        # Liste des transactions (adapté pour tenir sur 1 page)
        c.setFillColor(colors.black)
        c.setFont("Helvetica-Bold", 11)
        c.drawString(11*cm, height - 4*cm, "Détail des Transactions")
        
        # En-têtes de tableau
        c.setFillColor(colors.Color(0.2, 0.3, 0.4))
        c.rect(11*cm, height - 5.2*cm, 15*cm, 0.5*cm, fill=1)
        c.setFillColor(colors.white)
        c.setFont("Helvetica-Bold", 8)
        c.drawString(11.2*cm, height - 4.9*cm, "Date")
        c.drawString(13*cm, height - 4.9*cm, "Description")
        c.drawString(16*cm, height - 4.9*cm, "Catégorie")
        c.drawString(19*cm, height - 4.9*cm, "Type")
        c.drawString(21.5*cm, height - 4.9*cm, "Montant")
        
        # Transactions
        c.setFillColor(colors.black)
        c.setFont("Helvetica", 7.5)
        y = height - 5.5*cm
        line_height = 0.4*cm
        max_lines = 28  # Nombre max de lignes pour tenir sur 1 page
        
        count = 0
        for row in rows[:max_lines]:
            if y < 2*cm:
                break
            
            trans_date = str(row[0]) if row[0] else ''
            description = (str(row[1])[:20] if row[1] else '')
            category = (str(row[5])[:12] if row[5] else '')
            trans_type = str(row[3]) if row[3] else ''
            amount = format_number(row[2])
            payment = str(row[4]) if row[4] else 'cash'
            card_indicator = ' [C]' if payment == 'card' else ''
            
            c.drawString(11.2*cm, y, trans_date[5:] if len(trans_date) > 5 else trans_date)
            c.drawString(13*cm, y, description)
            c.drawString(16*cm, y, category)
            c.drawString(19*cm, y, trans_type[:3] + card_indicator)
            c.drawString(21.5*cm, y, amount)
            y -= line_height
            count += 1
        
        # Footer
        c.setFillColor(colors.gray)
        c.setFont("Helvetica-Oblique", 8)
        c.drawString(2*cm, 1.5*cm, f"Page 1/1 - Généré le {datetime.now().strftime('%d/%m/%Y à %H:%M')}")
        
        if len(rows) > max_lines:
            c.drawString(10*cm, 1.5*cm, f"({len(rows) - max_lines} transactions non affichées)")
        
        c.save()
        print(f"PDF generated successfully: {output_file}", file=sys.stderr)
        return 0
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        import traceback
        traceback.print_exc()
        return 1

if __name__ == "__main__":
    if len(sys.argv) >= 4:
        exit_code = generate_pdf(sys.argv[1], sys.argv[2], sys.argv[3])
        sys.exit(exit_code)
    else:
        print("Usage: export_pdf.py <user_id> <username> <output_file>", file=sys.stderr)
        sys.exit(1)