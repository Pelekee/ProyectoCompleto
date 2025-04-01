from litestar import Litestar, get
import pandas as pd
import os
from sqlalchemy import create_engine, Column, Integer, String, Float, ForeignKey
from sqlalchemy.orm import declarative_base, sessionmaker, relationship

# Configuración de la base de datos MySQL
DATABASE_URL = "mysql+mysqlconnector://root:bdd123@localhost/empresa"
Base = declarative_base()

class Cargo(Base):
    __tablename__ = "cargos"

    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(255))
    grado = Column(String(50))
    genero = Column(String(50))
    nacionalidad = Column(String(100))

    rentas = relationship("Renta", back_populates="cargo")

class Renta(Base):
    __tablename__ = "rentas"

    id = Column(Integer, primary_key=True, index=True)
    cargo_id = Column(Integer, ForeignKey("cargos.id"))
    renta_bruta = Column(Float)

    cargo = relationship("Cargo", back_populates="rentas")

# Crear el motor de base de datos
engine = create_engine(DATABASE_URL)
Base.metadata.create_all(bind=engine)

SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

UPLOAD_FOLDER = "public/archivos"
FILE_NAME = "PruebaTecnica.xlsx"

@get("/procesar" , sync_to_thread= True)
def procesar_excel() -> dict:
    try:
        file_path = os.path.join(UPLOAD_FOLDER, FILE_NAME)
        if not os.path.exists(file_path):
            return {"error": "El archivo no se encuentra en la carpeta 'public/archivos'."}

        df = pd.read_excel(file_path, sheet_name="ALTOS EJECUTIVOS", skiprows=6)
        df = df.loc[:, ~df.columns.duplicated()]
        df.columns = df.columns.str.strip()

        columnas_valores = [
            'Sueldo Base Mensual\n$', 'Gratificación Mensual\n$', 'Asignación Almuerzo \nMensual\n$',
            'Vales Almuerzo Valor Bruto Mensual\n$', 'Valor Casino \n Bruto\nMensual \n$', 'Asignación Movilización\nMensual\n$'
        ]
        columnas_info = ['Nombre del Cargo en la Empresa', 'Grado del Cargo según Evaluación Utilizada',
                         'Género\n(Masculino-Femenino)', 'Nacionalidad']

        columnas_encontradas = [col for col in (columnas_info + columnas_valores) if col in df.columns]
        df = df[columnas_encontradas]
        df[columnas_valores] = df[columnas_valores].apply(pd.to_numeric, errors='coerce').fillna(0)
        df["Renta Bruta Mensual"] = df[columnas_valores].sum(axis=1)
        df = df.dropna(how='all')
        df = df.head(256)
        df = df[(df.T != 0).any()]

        session = SessionLocal()
        for _, row in df.iterrows():
            nombre_cargo = str(row['Nombre del Cargo en la Empresa']).strip()
            grado_cargo = str(row['Grado del Cargo según Evaluación Utilizada']).strip()
            genero_cargo = str(row['Género\n(Masculino-Femenino)']).strip()
            nacionalidad_cargo = str(row['Nacionalidad']).strip()

            if pd.isna(nombre_cargo) or pd.isna(grado_cargo) or pd.isna(genero_cargo) or pd.isna(nacionalidad_cargo):
                continue

            cargo = Cargo(
                nombre=nombre_cargo,
                grado=grado_cargo,
                genero=genero_cargo,
                nacionalidad=nacionalidad_cargo
            )
            session.add(cargo)
            session.commit()

            renta = Renta(
                cargo_id=cargo.id,
                renta_bruta=row['Renta Bruta Mensual']
            )
            session.add(renta)
        
        session.commit()
        session.close()

        return df.to_dict(orient="records")
    except Exception as e:
        return {"error": str(e)}

app = Litestar(route_handlers=[procesar_excel])

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8001)
