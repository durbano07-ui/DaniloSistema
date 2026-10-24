#!/usr/bin/env python3
import os
import pty
import select
import sys
import subprocess

KEY_MAP = {
    10: '1', 11: '2', 12: '3', 13: '4', 14: '5', 15: '6', 16: '7', 17: '8', 18: '9', 19: '0',
    20: '-', 21: '=',
    24: 'q', 25: 'w', 26: 'e', 27: 'r', 28: 't', 29: 'y', 30: 'u', 31: 'i', 32: 'o', 33: 'p',
    38: 'a', 39: 's', 40: 'd', 41: 'f', 42: 'g', 43: 'h', 44: 'j', 45: 'k', 46: 'l',
    52: 'z', 53: 'x', 54: 'c', 55: 'v', 56: 'b', 57: 'n', 58: 'm',
    59: ',', 60: '.', 61: '-',
    82: '-', 86: '+',
    36: '\n', 104: '\n', 23: '\t'
}

SHIFT_KEY_MAP = {
    10: '!', 11: '"', 12: '#', 13: '$', 14: '%', 15: '&', 16: '/', 17: '(', 18: ')', 19: '=',
    20: '_',
    24: 'Q', 25: 'W', 26: 'E', 27: 'R', 28: 'T', 29: 'Y', 30: 'U', 31: 'I', 32: 'O', 33: 'P',
    38: 'A', 39: 'S', 40: 'D', 41: 'F', 42: 'G', 43: 'H', 44: 'J', 45: 'K', 46: 'L',
    52: 'Z', 53: 'X', 54: 'C', 55: 'V', 56: 'B', 57: 'N', 58: 'M',
}

def find_scanner_id():
    try:
        out = subprocess.check_output(["xinput", "list"], text=True, env={"DISPLAY": ":0"})
        for line in out.splitlines():
            if "Sunlux Barcode Scanner01" in line and "slave  keyboard" in line and "System Control" not in line and "Consumer" not in line:
                for part in line.split():
                    if part.startswith("id="):
                        return part.split("=")[1]
    except Exception:
        pass
    return "13"

def main():
    dev_id = find_scanner_id()
    print("=" * 60)
    print("  DIAGNÓSTICO EN VIVO: LECTOR 3nStar / Sunlux")
    print("=" * 60)
    print(f"Dispositivo activo: Sunlux Barcode Scanner01 (id={dev_id})")
    print("Captura en tiempo real: SIN BÚFER (Reactividad instantánea).\n")
    print("👉 DISPARA EL LECTOR AHORA SOBRE CUALQUIER CÓDIGO DE BARRAS...")
    print("(Presiona Ctrl+C para salir)\n")
    sys.stdout.flush()

    master, slave = pty.openpty()
    env = os.environ.copy()
    env["DISPLAY"] = ":0"

    proc = subprocess.Popen(
        ["xinput", "test", dev_id],
        stdin=slave,
        stdout=slave,
        stderr=slave,
        close_fds=True,
        env=env
    )
    os.close(slave)

    buffer = []
    shift_pressed = False
    line_buf = ""

    try:
        while True:
            r, _, _ = select.select([master], [], [], 0.5)
            if master in r:
                data = os.read(master, 1024).decode('utf-8', errors='ignore')
                if not data:
                    break
                line_buf += data
                while "\n" in line_buf:
                    line, line_buf = line_buf.split("\n", 1)
                    parts = line.strip().split()
                    if len(parts) >= 3 and parts[0] == "key":
                        action = parts[1]
                        try:
                            keycode = int(parts[2])
                        except ValueError:
                            continue

                        if keycode in (50, 62):
                            shift_pressed = (action == "press")
                            continue

                        if action == "press":
                            if shift_pressed and keycode in SHIFT_KEY_MAP:
                                char = SHIFT_KEY_MAP[keycode]
                            elif keycode in KEY_MAP:
                                char = KEY_MAP[keycode]
                            else:
                                char = f"[{keycode}]"

                            if char == '\n':
                                code_str = "".join(buffer)
                                print(f"\n\n🎯 [CÓDIGO CAPTURADO CON ENTER]: '{code_str}'")
                                print(f"   Longitud: {len(code_str)} caracteres\n")
                                print("👉 DISPARA OTRA VEZ (o Ctrl+C para salir)...")
                                sys.stdout.flush()
                                buffer = []
                            elif char == '\t':
                                code_str = "".join(buffer)
                                print(f"\n\n🎯 [CÓDIGO CAPTURADO CON TAB]: '{code_str}'")
                                print(f"   Longitud: {len(code_str)} caracteres\n")
                                print("👉 DISPARA OTRA VEZ (o Ctrl+C para salir)...")
                                sys.stdout.flush()
                                buffer = []
                            else:
                                buffer.append(char)
                                sys.stdout.write(char)
                                sys.stdout.flush()
    except KeyboardInterrupt:
        print("\n\nPrueba finalizada.")
    finally:
        proc.kill()
        os.close(master)

if __name__ == "__main__":
    main()
