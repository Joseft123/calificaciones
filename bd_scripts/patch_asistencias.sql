CREATE TABLE IF NOT EXISTS asistencias (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno INT NOT null,
    id_materia INT NOT null,
    id_docente INT NOT null,
    fecha DATE NOT null,
    estado ENUM(
        'Presente',
        'Falta',
        'Retardo'
    ) NOT null DEFAULT 'Presente',
    FOREIGN KEY (id_alumno) REFERENCES alumnos (id_alumno) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materias (id_materia) ON DELETE CASCADE,
    FOREIGN KEY (id_docente) REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    UNIQUE KEY (
        id_alumno,
        id_materia,
        id_docente,
        fecha
    )
);