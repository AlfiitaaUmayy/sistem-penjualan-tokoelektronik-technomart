<?php
class Keranjang
{
    public function tambah($barang, $qty)
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $id = $barang['id'];

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$id] = [
                'id'    => $barang['id'],
                'nama'  => $barang['nama_barang'],
                'harga' => $barang['harga'],
                'qty'   => $qty,
                'foto'  => $barang['foto']
            ];
        }
    }

    public function hapus($id)
    {
        unset($_SESSION['cart'][$id]);
    }

    public function getAll()
    {
        return $_SESSION['cart'] ?? [];
    }
}
