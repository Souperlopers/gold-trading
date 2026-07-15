"use client";

import {useState} from 'react';
import Input from '@/app/_components/ui/Input';
import Button from '@/app/_components/ui/Button';
import { useMutation } from '@tanstack/react-query';
import axios from 'axios';

const login = async (email: string, password: string) => {
    const credentials = { username: 'john_doe', password: 'pass123' };
    const response = await axios.post('https://fakestoreapi.com/auth/login', credentials);
  return response.data;
};


const Page = () => {
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");

    const mutation = useMutation({
        mutationFn: ({ username, password }:{username:string, password:string}) => login(username, password),
    })

    return (
        <div className='bg-background flex items-center justify-center w-full h-dvh'>
            <div>
                <h1 className='text-3xl font-medium text-center mb-20'>
                    LOGIN PAGE
                </h1>
                <div className='flex flex-col gap-10'>
                    <div className='flex flex-col'>
                    <Input label='نام کاربری' type='text' onChange={(e)=>setUsername(e.target.value)}/>
                    <Input label='رمز عبور' type='text' onChange={(e)=>setPassword(e.target.value)}/>
                    </div>
                    <Button title='ورود' onClick={() => mutation.mutate({username,password})}/>
                </div>
            </div>
        </div>
    );
}

export default Page;
